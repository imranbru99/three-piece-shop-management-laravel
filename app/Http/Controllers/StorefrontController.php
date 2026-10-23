<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Shop;
use App\Services\SalesService;
use App\Support\BdPhone;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StorefrontController extends Controller
{
    protected function shop(string $slug): Shop
    {
        $shop = Shop::query()->where('slug', $slug)->firstOrFail();
        abort_unless($shop->enabled('storefront'), 404);

        return $shop;
    }

    public function index(Request $request, string $slug): View
    {
        $shop = $this->shop($slug);
        $search = mb_substr((string) $request->query('q', ''), 0, 100);
        $products = Product::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('active', true)
            ->when($search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('name_bn', 'like', '%'.$search.'%')->orWhere('fabric', 'like', '%'.$search.'%')))->orderBy('id')->paginate(12)->withQueryString();

        return view('public.storefront', compact('shop', 'products', 'search') + ['title' => $shop->name]);
    }

    public function product(string $slug, string $productSlug): View
    {
        $shop = $this->shop($slug);
        $product = Product::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('slug', $productSlug)->where('active', true)->firstOrFail();

        return view('public.product', compact('shop', 'product') + ['title' => app()->getLocale() === 'bn' ? $product->name_bn : $product->name, 'description' => $product->description]);
    }

    public function checkout(Request $request, string $slug, string $productSlug, SalesService $sales): RedirectResponse
    {
        $shop = $this->shop($slug);
        $product = Product::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('slug', $productSlug)->where('active', true)->firstOrFail();
        $details = $request->validate(['name' => 'required|string|max:100', 'phone' => 'required|string|max:30', 'address' => 'required|string|min:8|max:500', 'district' => ['required', Rule::in(self::districts())], 'quantity' => 'required|integer|min:1|max:10', 'idempotency_key' => 'required|uuid', 'website' => 'nullable|max:0']);
        $order = $sales->create($shop, [$product->id => (int) $details['quantity']], $details, 'storefront', $details['idempotency_key']);
        // Never put a customer's phone in a shareable URL.
        $request->session()->put('receipt.'.$order->number, $order->id);

        return redirect()->route('store.receipt', [$shop->slug, $order->number]);
    }

    public function receipt(Request $request, string $slug, string $number): View
    {
        $shop = $this->shop($slug);
        $order = Order::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('number', $number)->firstOrFail();
        abort_unless($request->session()->get('receipt.'.$number) === $order->id, 404);
        $customer = Customer::withoutGlobalScope('shop')->where('shop_id', $shop->id)->findOrFail($order->customer_id);
        $lines = OrderLine::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('order_id', $order->id)->get();

        return view('public.receipt', compact('shop', 'order', 'customer', 'lines'));
    }

    public function track(Request $request, string $slug): View
    {
        $shop = $this->shop($slug);
        $order = null;
        if ($request->isMethod('post')) {
            $data = $request->validate(['number' => 'required|string|max:30', 'phone' => 'required|string|max:30']);
            $phone = BdPhone::normalize($data['phone']);
            $customerIds = Customer::withoutGlobalScope('shop')->where('shop_id', $shop->id)->where('phone', $phone)->select('id');
            $order = Order::withoutGlobalScope('shop')->where('shop_id', $shop->id)->whereIn('customer_id', $customerIds)->where('number', $data['number'])->first();
        }

        return view('public.track', compact('shop', 'order'));
    }

    /** @return list<string> */
    public static function districts(): array
    {
        return ['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Gazipur', 'Narayanganj', 'Other'];
    }
}
