<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\JournalLine;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockMovement;
use App\Support\BdPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesService
{
    /**
     * @param  array<int,int>  $items
     * @param  array<string,mixed>  $details
     */
    public function create(Shop $shop, array $items, array $details, string $source, string $key): Order
    {
        return DB::transaction(function () use ($shop, $items, $details, $source, $key) {
            // Serializing on the tenant makes retries deterministic even before an order exists.
            Shop::query()->whereKey($shop->id)->lockForUpdate()->firstOrFail();
            if ($existing = Order::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('idempotency_key', $key)->first()) {
                return $existing;
            }
            $phone = BdPhone::normalize($details['phone']);
            $customer = Customer::withoutGlobalScope('shop')->updateOrCreate(['shop_id' => $shop->id, 'phone' => $phone], ['name' => $details['name'], 'address' => $details['address'], 'district' => $details['district']]);
            $products = Product::withoutGlobalScope('shop')->where('shop_id', $shop->id)->whereIn('id', array_keys($items))->where('active', true)->orderBy('id')->lockForUpdate()->get();
            if ($products->count() !== count($items) || ! count($items)) {
                throw ValidationException::withMessages(['items' => 'Select valid products from this shop.']);
            }
            $subtotal = 0;
            foreach ($products as $product) {
                $quantity = (int) $items[$product->id];
                if ($quantity < 1 || $quantity > 99) {
                    throw ValidationException::withMessages(['items' => 'Quantity must be between 1 and 99.']);
                }
                if ($shop->enabled('inventory') && $product->stock < $quantity) {
                    throw ValidationException::withMessages(['items' => "Insufficient stock: {$product->name}"]);
                }
                $subtotal += $quantity * $product->price;
            }
            $delivery = $source === 'pos' ? 0 : ($details['district'] === 'Dhaka' ? 6000 : 12000);
            $order = Order::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'customer_id' => $customer->id, 'number' => 'TP-'.strtoupper(Str::random(10)), 'source' => $source, 'status' => $source === 'pos' ? 'confirmed' : 'pending', 'subtotal' => $subtotal, 'delivery_charge' => $delivery, 'total' => $subtotal + $delivery, 'paid' => 0, 'stock_committed' => $source === 'pos' && $shop->enabled('inventory'), 'payment_method' => $details['method'] ?? 'cod', 'idempotency_key' => $key, 'notes' => $details['notes'] ?? null]);
            foreach ($products as $product) {
                $quantity = $items[$product->id];
                OrderLine::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'order_id' => $order->id, 'product_id' => $product->id, 'name' => $product->name, 'quantity' => $quantity, 'unit_price' => $product->price, 'unit_cost' => $product->cost]);
                if ($source === 'pos' && $shop->enabled('inventory')) {
                    $this->move($shop, $product, -$quantity, 'POS sale', $order->id);
                }
            }
            OrderEvent::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'order_id' => $order->id, 'status' => $order->status, 'note' => 'Order created']);
            if ($source === 'pos') {
                $this->recognizeSale($order);
                $order->update(['status' => 'delivered']);
                $cash = (int) ($details['cash_amount'] ?? 0);
                $bkash = (int) ($details['bkash_amount'] ?? 0);
                if ($cash + $bkash !== $order->total) {
                    throw ValidationException::withMessages(['payment' => 'Cash and bKash amounts must equal the order total exactly.']);
                }
                if ($cash > 0) {
                    $this->pay($shop, $order, $cash, 'cash', Str::uuid()->toString());
                }
                if ($bkash > 0) {
                    $this->pay($shop, $order, $bkash, 'bkash', Str::uuid()->toString());
                }
            }

            return $order->refresh();
        });
    }

    public function transition(Shop $shop, Order $order, string $status): void
    {
        DB::transaction(function () use ($shop, $order, $status) {
            $order = Order::withoutGlobalScope('shop')->where('shop_id', $shop->id)->lockForUpdate()->findOrFail($order->id);
            if ($order->status === $status) {
                return;
            }
            $allowed = ['pending' => ['confirmed', 'on_hold', 'cancelled'], 'on_hold' => ['confirmed', 'cancelled'], 'confirmed' => ['packed', 'cancelled'], 'packed' => ['shipped', 'cancelled'], 'shipped' => ['delivered'], 'delivered' => [], 'cancelled' => []];
            if (! in_array($status, $allowed[$order->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'This order transition is not permitted.']);
            }
            $stockChange = $status === 'confirmed' ? -1 : ($status === 'cancelled' && $order->stock_committed ? 1 : 0);
            $lines = OrderLine::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('order_id', $order->id)->orderBy('product_id')->get();
            if ($stockChange && ($shop->enabled('inventory') || $order->stock_committed)) {
                foreach ($lines as $line) {
                    $product = Product::withoutGlobalScope('shop')->where('shop_id', $shop->id)->lockForUpdate()->findOrFail($line->product_id);
                    if ($stockChange < 0 && $product->stock < $line->quantity) {
                        throw ValidationException::withMessages(['stock' => 'Insufficient stock to confirm this order.']);
                    }
                    $this->move($shop, $product, $stockChange * $line->quantity, 'Order '.$status, $order->id);
                }
            }
            if ($status === 'delivered') {
                $this->recognizeSale($order);
            }
            $order->update(['status' => $status, 'stock_committed' => $status === 'cancelled' ? false : ($order->stock_committed || ($status === 'confirmed' && $shop->enabled('inventory')))]);
            OrderEvent::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'order_id' => $order->id, 'status' => $status, 'note' => 'Status updated']);
        });
    }

    public function pay(Shop $shop, Order $order, int $amount, string $method, string $key, ?string $reference = null): void
    {
        DB::transaction(function () use ($shop, $order, $amount, $method, $key, $reference) {
            $order = Order::withoutGlobalScope('shop')->where('shop_id', $shop->id)->lockForUpdate()->findOrFail($order->id);
            if (Payment::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('idempotency_key', $key)->exists()) {
                return;
            }
            if ($order->status !== 'delivered' || $amount < 1 || $amount > $order->total - $order->paid) {
                throw ValidationException::withMessages(['amount' => 'Payment must be positive, no more than the delivered order balance.']);
            }
            Payment::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'order_id' => $order->id, 'amount' => $amount, 'method' => $method, 'reference' => $reference, 'idempotency_key' => $key]);
            $order->increment('paid', $amount);
            $event = 'payment:'.$key;
            $this->journal($order, $event, $method, $amount, 0, 'Payment received');
            $this->journal($order, $event, 'receivable', 0, $amount, 'Payment received');
        });
    }

    public function adjust(Shop $shop, Product $product, int $quantity, string $reason): void
    {
        DB::transaction(function () use ($shop, $product, $quantity, $reason) {
            $product = Product::withoutGlobalScope('shop')->where('shop_id', $shop->id)->lockForUpdate()->findOrFail($product->id);
            if ($product->stock + $quantity < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stock cannot become negative.']);
            }
            $this->move($shop, $product, $quantity, $reason);
        });
    }

    protected function move(Shop $shop, Product $product, int $quantity, string $reason, ?int $orderId = null): void
    {
        $product->increment('stock', $quantity);
        StockMovement::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'order_id' => $orderId, 'quantity' => $quantity, 'reason' => $reason]);
    }

    protected function recognizeSale(Order $order): void
    {
        $event = 'sale:'.$order->id;
        if (JournalLine::withoutGlobalScope('shop')->where('shop_id', $order->shop_id)->where('event_key', $event)->exists()) {
            return;
        }
        $cost = OrderLine::withoutGlobalScope('shop')->where('shop_id', $order->shop_id)->where('order_id', $order->id)->get()->sum(fn ($line) => $line->unit_cost * $line->quantity);
        $this->journal($order, $event, 'receivable', $order->total, 0, 'Sale recognized');
        $this->journal($order, $event, 'sales', 0, $order->subtotal, 'Sale recognized');
        if ($order->delivery_charge) {
            $this->journal($order, $event, 'delivery_income', 0, $order->delivery_charge, 'Delivery charge');
        }
        $this->journal($order, $event, 'cost_of_goods', $cost, 0, 'Cost of goods sold');
        $this->journal($order, $event, 'inventory', 0, $cost, 'Stock released');
    }

    protected function journal(Order $order, string $event, string $account, int $debit, int $credit, string $description): void
    {
        JournalLine::withoutGlobalScope('shop')->create(['shop_id' => $order->shop_id, 'order_id' => $order->id, 'event_key' => $event, 'account' => $account, 'debit' => $debit, 'credit' => $credit, 'description' => $description]);
    }
}
