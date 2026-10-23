<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\JournalLine;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Modules\ModuleRegistry;
use App\Services\SalesService;
use App\Support\BusinessAccess;
use App\Support\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ImranDev\UnicodePdf\Facades\UnicodePdf;
use ImranDev\UniversalSlug\UniversalSlug;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkspaceController extends Controller
{
    protected function shop(Request $request, string $slug, string $module): Shop
    {
        abort_unless($request->user() !== null, 403);
        $shop = Shop::query()->where('slug', $slug)->firstOrFail();
        BusinessAccess::check($request->user(), $shop, $module);

        return $shop;
    }

    public function index(Request $request, string $slug, string $section = 'dashboard'): View
    {
        $shop = $this->shop($request, $slug, $section);
        $search = mb_substr((string) $request->query('q', ''), 0, 100);
        $data = ['shop' => $shop, 'section' => $section, 'allowed' => BusinessAccess::allowed($request->user()->role), 'search' => $search];
        if ($section === 'dashboard' || $section === 'reports' || $section === 'accounts') {
            $data['sales'] = Order::query()->where('status', 'delivered')->sum('total');
            $data['orderCount'] = Order::query()->count();
            $data['pending'] = Order::query()->whereIn('status', ['pending', 'on_hold'])->count();
            $data['lowStock'] = Product::query()->whereColumn('stock', '<=', 'reorder_level')->count();
            $data['chart'] = Order::query()->where('status', 'delivered')->where('created_at', '>=', now()->subDays(6)->startOfDay())->get(['total', 'created_at'])->groupBy(fn ($order) => $order->created_at->format('Y-m-d'))->map(fn ($orders) => $orders->sum('total'));
            $data['recent'] = Order::query()->with('customer')->latest()->limit(7)->get();
            $data['balances'] = JournalLine::query()->select('account')->selectRaw('SUM(debit) AS debits, SUM(credit) AS credits')->groupBy('account')->get();
            $data['journals'] = JournalLine::query()->latest()->paginate(20);
        }
        if (in_array($section, ['catalog', 'inventory', 'pos'], true)) {
            $data['products'] = Product::query()->when($search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('sku', 'like', '%'.$search.'%')))->orderBy('id')->paginate($section === 'pos' ? 60 : 15)->withQueryString();
        }
        if ($section === 'orders' || $section === 'payments') {
            $status = $request->query('status');
            $data['orders'] = Order::query()->with('customer')->when($status && in_array($status, ['pending', 'confirmed', 'on_hold', 'packed', 'shipped', 'delivered', 'cancelled'], true), fn ($q) => $q->where('status', $status))->when($search, fn ($q) => $q->where('number', 'like', '%'.$search.'%'))->latest()->paginate(15)->withQueryString();
        }
        if ($section === 'customers') {
            $data['customers'] = Customer::query()->when($search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%')))->latest()->paginate(20)->withQueryString();
        }
        if ($section === 'modules') {
            $data['registry'] = app(ModuleRegistry::class)->all();
        }

        return view('workspace.index', $data);
    }

    public function createProduct(Request $request, string $slug, SalesService $sales): RedirectResponse
    {
        $shop = $this->shop($request, $slug, 'catalog');
        abort_unless(in_array($request->user()->role, ['owner', 'manager'], true), 403);
        $data = $request->validate(['name' => 'required|string|max:100', 'name_bn' => 'required|string|max:100', 'sku' => ['required', 'alpha_dash', 'max:40', Rule::unique('products')->where('shop_id', $shop->id)], 'form' => ['required', Rule::in(['unstitched', 'semi_stitched', 'ready_made', 'custom_stitch'])], 'fabric' => 'required|string|max:40', 'color' => 'required|string|max:40', 'size' => 'required|string|max:20', 'price' => 'required|integer|min:1|max:10000000', 'cost' => 'required|integer|min:0|max:10000000', 'stock' => 'required|integer|min:0|max:10000']);
        DB::transaction(function () use ($shop, $data, $sales) {
            $stock = $data['stock'];
            $data['stock'] = 0;
            $data['slug'] = UniversalSlug::of($data['name'])->mode('ascii_only')->maxLength(100)->toString().'-'.strtolower(Str::random(5));
            $product = Product::query()->create($data);
            if ($stock) {
                $sales->adjust($shop, $product, (int) $stock, 'Opening stock');
            }
        });

        return back()->with('success', Language::text('Product saved. Prices are stored in poisha.', 'পণ্য সংরক্ষিত হয়েছে। দাম পয়সায় সংরক্ষিত।'));
    }

    public function stock(Request $request, string $slug, int $id, SalesService $sales): RedirectResponse
    {
        $shop = $this->shop($request, $slug, 'inventory');
        $data = $request->validate(['quantity' => 'required|integer|between:-10000,10000|not_in:0', 'reason' => 'required|string|min:5|max:150']);
        $product = Product::query()->findOrFail($id);
        $sales->adjust($shop, $product, (int) $data['quantity'], $data['reason']);

        return back()->with('success', Language::text('Stock adjusted and recorded in the movement ledger.', 'মজুদ পরিবর্তন ও খতিয়ানে সংরক্ষিত হয়েছে।'));
    }

    public function status(Request $request, string $slug, int $id, SalesService $sales): RedirectResponse
    {
        $shop = $this->shop($request, $slug, 'orders');
        abort_unless(in_array($request->user()->role, ['owner', 'manager', 'agent'], true), 403);
        $data = $request->validate(['status' => ['required', Rule::in(['confirmed', 'on_hold', 'cancelled', 'packed', 'shipped', 'delivered'])]]);
        $sales->transition($shop, Order::query()->findOrFail($id), $data['status']);

        return back()->with('success', Language::text('Order status updated.', 'অর্ডারের অবস্থা পরিবর্তিত হয়েছে।'));
    }

    public function payment(Request $request, string $slug, int $id, SalesService $sales): RedirectResponse
    {
        $shop = $this->shop($request, $slug, 'payments');
        $data = $request->validate(['amount' => 'required|integer|min:1|max:100000000', 'method' => ['required', Rule::in(['cash', 'bkash', 'nagad', 'bank'])], 'reference' => 'nullable|string|max:100', 'idempotency_key' => 'required|uuid']);
        $sales->pay($shop, Order::query()->findOrFail($id), (int) $data['amount'], $data['method'], $data['idempotency_key'], $data['reference'] ?? null);

        return back()->with('success', Language::text('Payment recorded.', 'পেমেন্ট সংরক্ষিত হয়েছে।'));
    }

    public function pos(Request $request, string $slug, SalesService $sales): RedirectResponse
    {
        $shop = $this->shop($request, $slug, 'pos');
        $data = $request->validate(['items' => 'required|array|max:60', 'items.*' => 'nullable|integer|min:0|max:99', 'name' => 'required|string|max:100', 'phone' => 'required|string|max:30', 'cash_amount' => 'required|integer|min:0|max:100000000', 'bkash_amount' => 'required|integer|min:0|max:100000000', 'idempotency_key' => 'required|uuid']);
        $items = array_map('intval', array_filter($data['items'], fn ($q) => (int) $q > 0));
        $data['address'] = 'Counter collection';
        $data['district'] = 'Dhaka';
        $order = $sales->create($shop, $items, $data, 'pos', $data['idempotency_key']);

        return redirect()->route('workspace.receipt', [$slug, $order->id]);
    }

    public function receipt(Request $request, string $slug, int $id): View
    {
        $shop = $this->shop($request, $slug, 'orders');
        $order = Order::query()->with(['lines', 'customer'])->findOrFail($id);
        $customer = $order->customer;
        $lines = $order->lines;

        return view('public.receipt', compact('shop', 'order', 'customer', 'lines'));
    }

    public function pdf(Request $request, string $slug, int $id): Response
    {
        $shop = $this->shop($request, $slug, 'orders');
        $order = Order::query()->with(['lines', 'customer'])->findOrFail($id);

        return UnicodePdf::preset('bengali')
            ->loadView('pdf.receipt', ['shop' => $shop, 'order' => $order])
            ->a4()->download($order->number.'.pdf');
    }

    public function toggle(Request $request, string $slug, string $key, ModuleRegistry $registry): RedirectResponse
    {
        $shop = $this->shop($request, $slug, 'modules');
        $module = $registry->get($key);
        abort_if($module['locked'], 422);
        DB::transaction(function () use ($shop, $key, $registry) {
            $shop = Shop::query()->lockForUpdate()->findOrFail($shop->id);
            $enabled = $shop->modules;
            if (in_array($key, $enabled, true)) {
                foreach ($enabled as $other) {
                    if (in_array($key, $registry->get($other)['depends_on'], true)) {
                        throw ValidationException::withMessages(['module' => "Disable {$other} first; it depends on {$key}."]);
                    }
                }
                $enabled = array_values(array_diff($enabled, [$key]));
            } else {
                $add = function (string $key) use (&$add, &$enabled, $registry): void {
                    foreach ($registry->get($key)['depends_on'] as $dep) {
                        $add($dep);
                    } $enabled[] = $key;
                };
                $add($key);
                $enabled = array_values(array_unique($enabled));
            }
            $shop->update(['modules' => $enabled]);
        });

        return back()->with('success', Language::text('Module setting saved. Existing data is retained.', 'মডিউল সেটিংস সংরক্ষিত। পুরনো তথ্য অক্ষত আছে।'));
    }

    public function export(Request $request, string $slug): StreamedResponse
    {
        $this->shop($request, $slug, 'reports');

        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order', 'Source', 'Status', 'Total BDT', 'Paid BDT', 'Created'], ',', '"', '');
            Order::query()->orderBy('id')->chunkById(200,function ($orders) use ($file) {
                foreach ($orders as $order) {
                    fputcsv($file,[$order->number, $order->source, $order->status, number_format($order->total / 100,2,'.',''), number_format($order->paid / 100,2,'.',''), $order->created_at->toIso8601String()],',','"','');
                }
            });
            fclose($file);
        }, 'sales.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
