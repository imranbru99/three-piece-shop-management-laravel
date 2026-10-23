<?php

use App\Models\JournalLine;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\SalesService;
use App\Support\BdPhone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function sampleShop(string $slug = 'sample'): Shop
{
    return Shop::create(['name' => 'Sample '.$slug, 'name_bn' => 'নমুনা', 'slug' => $slug, 'preset' => 'boutique_retail', 'city' => 'Dhaka', 'modules' => ['core', 'catalog', 'customers', 'orders', 'inventory', 'storefront', 'payments', 'pos', 'accounts', 'reports'], 'is_demo' => true]);
}
function sampleUser(Shop $shop, string $role = 'owner'): User
{
    return User::factory()->create(['shop_id' => $shop->id, 'role' => $role]);
}
function sampleProduct(Shop $shop, int $stock = 10): Product
{
    return Product::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'name' => 'Cotton set', 'name_bn' => 'কটন সেট', 'slug' => 'cotton-set', 'sku' => 'SKU-1', 'form' => 'ready_made', 'fabric' => 'Cotton', 'color' => 'Forest', 'size' => 'M', 'price' => 185000, 'cost' => 100000, 'stock' => $stock]);
}
function sampleDetails(): array
{
    return ['name' => 'Sample recipient', 'phone' => '+8801700000001', 'address' => 'Fictional street 1, sample house', 'district' => 'Dhaka', 'cash_amount' => 100000, 'bkash_amount' => 85000];
}

it('completes storefront confirmation delivery and payment with balanced accounting', function () {
    $shop = sampleShop();
    $product = sampleProduct($shop);
    $sales = app(SalesService::class);
    $key = Str::uuid()->toString();
    $order = $sales->create($shop, [$product->id => 2], sampleDetails(), 'storefront', $key);
    expect($order->total)->toBe(376000)->and($product->fresh()->stock)->toBe(10);
    expect($sales->create($shop, [$product->id => 2], sampleDetails(), 'storefront', $key)->id)->toBe($order->id);
    foreach (['confirmed', 'packed', 'shipped', 'delivered'] as $status) {
        $sales->transition($shop, $order, $status);
    }
    expect($product->fresh()->stock)->toBe(8);
    $paymentKey = Str::uuid()->toString();
    $sales->pay($shop, $order, 376000, 'cash', $paymentKey);
    $sales->pay($shop, $order, 376000, 'cash', $paymentKey);
    expect($order->fresh()->paid)->toBe(376000);
    expect(Payment::withoutGlobalScope('shop')->count())->toBe(1);
    expect(JournalLine::withoutGlobalScope('shop')->sum('debit'))->toBe(JournalLine::withoutGlobalScope('shop')->sum('credit'));
    expect(JournalLine::withoutGlobalScope('shop')->where('account', 'cost_of_goods')->sum('debit'))->toBe(200000);
});

it('makes an atomic split-payment POS sale and rolls back invalid payment totals', function () {
    $shop = sampleShop();
    $product = sampleProduct($shop);
    $sales = app(SalesService::class);
    $order = $sales->create($shop, [$product->id => 1], sampleDetails(), 'pos', Str::uuid()->toString());
    expect($order->status)->toBe('delivered')->and($order->paid)->toBe(185000)->and($product->fresh()->stock)->toBe(9);
    $details = sampleDetails();
    $details['cash_amount'] = 1;
    $details['bkash_amount'] = 1;
    try {
        $sales->create($shop, [$product->id => 1], $details, 'pos', Str::uuid()->toString());
    } catch (ValidationException) {
    }
    expect(Order::withoutGlobalScope('shop')->count())->toBe(1)->and($product->fresh()->stock)->toBe(9);
});

it('rejects overselling invalid transitions and overpayments', function () {
    $shop = sampleShop();
    $product = sampleProduct($shop, 1);
    $sales = app(SalesService::class);
    expect(fn () => $sales->create($shop, [$product->id => 2], sampleDetails(), 'pos', Str::uuid()->toString()))->toThrow(ValidationException::class);
    $order = $sales->create($shop, [$product->id => 1], sampleDetails(), 'storefront', Str::uuid()->toString());
    expect(fn () => $sales->transition($shop, $order, 'delivered'))->toThrow(ValidationException::class);
    expect(fn () => $sales->pay($shop, $order, 999999, 'cash', Str::uuid()->toString()))->toThrow(ValidationException::class);
    expect($product->fresh()->stock)->toBe(1);
});

it('restores committed stock on cancel even if inventory was disabled after confirmation', function () {
    $shop = sampleShop();
    $product = sampleProduct($shop);
    $sales = app(SalesService::class);
    $order = $sales->create($shop, [$product->id => 2], sampleDetails(), 'storefront', Str::uuid()->toString());
    $sales->transition($shop, $order, 'confirmed');
    $shop->update(['modules' => ['catalog', 'orders', 'customers']]);
    $sales->transition($shop, $order, 'cancelled');
    expect($product->fresh()->stock)->toBe(10)->and($order->fresh()->stock_committed)->toBeFalse();
});

it('does not add phantom stock when inventory is enabled after an untracked order', function () {
    $shop = sampleShop();
    $shop->update(['modules' => ['catalog', 'orders', 'customers']]);
    $product = sampleProduct($shop);
    $sales = app(SalesService::class);
    $order = $sales->create($shop, [$product->id => 2], sampleDetails(), 'storefront', Str::uuid()->toString());
    $sales->transition($shop, $order, 'confirmed');
    $shop->update(['modules' => ['catalog', 'orders', 'customers', 'inventory']]);
    $sales->transition($shop, $order, 'cancelled');
    expect($product->fresh()->stock)->toBe(10);
});

it('fails closed for tenant model queries and isolates workspace writes receipts and exports', function () {
    $a = sampleShop('a');
    $b = sampleShop('b');
    $pa = sampleProduct($a);
    $pb = sampleProduct($b);
    $owner = sampleUser($a);
    expect(Product::query()->count())->toBe(0);
    $this->actingAs($owner);
    expect(Product::query()->pluck('id')->all())->toBe([$pa->id]);
    $this->get('/app/b/catalog')->assertForbidden();
    $this->post('/app/a/inventory/'.$pb->id, ['quantity' => 1, 'reason' => 'Sample adjustment'])->assertNotFound();
    $sales = app(SalesService::class);
    $other = $sales->create($b, [$pb->id => 1], sampleDetails(), 'storefront', Str::uuid()->toString());
    $this->get('/app/a/orders/'.$other->id.'/receipt')->assertNotFound();
    $this->post('/app/a/orders/'.$other->id.'/status', ['status' => 'confirmed'])->assertNotFound();
    $this->get('/app/b/reports/export/csv')->assertForbidden();
    $this->get('/app/a/catalog')->assertOk()->assertSee($pa->name);
});

it('enforces cashier permissions and nondestructive module toggles', function () {
    $shop = sampleShop();
    $product = sampleProduct($shop);
    $cashier = sampleUser($shop, 'cashier');
    $this->actingAs($cashier)->post('/app/sample/inventory/'.$product->id, ['quantity' => 1, 'reason' => 'Sample adjustment'])->assertForbidden();
    $this->post('/app/sample/modules/pos')->assertForbidden();
    $this->get('/app/sample/accounts')->assertForbidden();
    $this->actingAs(sampleUser($shop, 'owner'))->post('/app/sample/modules/pos')->assertRedirect();
    $this->get('/app/sample/pos')->assertNotFound();
    expect(Product::query()->count())->toBe(1);
    $this->post('/app/sample/modules/pos')->assertRedirect();
    $this->get('/app/sample/pos')->assertOk();
    $this->post('/app/sample/modules/catalog')->assertStatus(422);
});

it('protects storefront receipts by session and tracks only with a matching phone', function () {
    $shop = sampleShop();
    $product = sampleProduct($shop);
    $data = sampleDetails() + ['quantity' => 1, 'idempotency_key' => Str::uuid()->toString()];
    $this->post('/s/sample/product/cotton-set', $data)->assertRedirect();
    $order = Order::withoutGlobalScope('shop')->firstOrFail();
    $this->get('/s/sample/receipt/'.$order->number)->assertOk();
    $this->flushSession();
    $this->get('/s/sample/receipt/'.$order->number)->assertNotFound();
    $this->post('/s/sample/track', ['number' => $order->number, 'phone' => '01700000002'])->assertOk()->assertSee('No order matches');
    $this->post('/s/sample/track', ['number' => $order->number, 'phone' => '01700000001'])->assertOk()->assertSee('pending');
});

it('normalizes Bangladesh phone forms including Bangla numerals', function () {
    expect(BdPhone::normalize('০১৭০০০০০০০১'))->toBe('01700000001');
    expect(BdPhone::normalize('+88 01700-000001'))->toBe('01700000001');
    expect(fn () => BdPhone::normalize('012345'))->toThrow(ValidationException::class);
});

it('opens all implemented screens for authorized demo roles', function () {
    $shop = sampleShop();
    sampleProduct($shop);
    $this->actingAs(sampleUser($shop));
    foreach (['dashboard', 'catalog', 'orders', 'customers', 'pos', 'inventory', 'payments', 'accounts', 'reports', 'modules'] as $screen) {
        $this->get('/app/sample/'.$screen)->assertOk();
    }
});

it('downloads a scoped Bengali receipt through Unicode PDF', function () {
    $shop = sampleShop();
    $product = sampleProduct($shop);
    $this->actingAs(sampleUser($shop));
    $order = app(SalesService::class)->create($shop, [$product->id => 1], sampleDetails(), 'pos', Str::uuid()->toString());
    $response = $this->get('/app/sample/orders/'.$order->id.'/pdf')->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
    expect($response->getContent())->toStartWith('%PDF-');
});
