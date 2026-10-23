<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\StockMovement;
use App\Models\User;
use App\Modules\ModuleRegistry;
use App\Services\SalesService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('Demo seeding is prohibited in production.');
        }
        $registry = app(ModuleRegistry::class);
        $sales = app(SalesService::class);
        $shops = [['Noor Boutique', 'নূর বুটিক', 'noor', 'boutique_retail', 'Dhaka', 60, 400], ['Rongdhonu Online', 'রংধনু অনলাইন', 'rongdhonu', 'online_seller', 'Chattogram', 40, 110], ['Dhaka Fabrics Wholesale', 'ঢাকা ফ্যাব্রিকস হোলসেল', 'dhaka-fabrics', 'wholesale_importer', 'Dhaka', 32, 80], ['Sundori Tailors', 'সুন্দরী টেইলার্স', 'sundori', 'manufacturer_tailor', 'Sylhet', 24, 50]];
        foreach ($shops as [$name,$bn,$slug,$preset,$city,$productCount,$orderCount]) {
            if (Shop::query()->where('slug', $slug)->exists()) {
                continue;
            }
            DB::transaction(function () use ($name, $bn, $slug, $preset, $city, $productCount, $orderCount, $registry, $sales) {
                $enabled = array_values(array_unique(array_merge(['core', 'catalog', 'orders', 'customers', 'storefront'], $registry->presets()[$preset]['modules'])));
                $shop = Shop::query()->create(['name' => $name, 'name_bn' => $bn, 'slug' => $slug, 'preset' => $preset, 'city' => $city, 'modules' => $enabled, 'is_demo' => true]);
                foreach (['owner', 'manager', 'cashier', 'agent', 'stock', 'accounts'] as $role) {
                    User::query()->create(['name' => ucfirst($role).' · '.$name, 'email' => $role.'@'.$slug.'.demo.test', 'password' => 'password', 'shop_id' => $shop->id, 'role' => $role]);
                }
                $products = [];
                $designs = [['Jamdani cotton three-piece', 'জামদানি কটন থ্রি-পিস', 'Cotton', 245000, 138000], ['Printed lawn unstitched', 'লন প্রিন্ট আনস্টিচড', 'Lawn', 185000, 105000], ['Embroidered linen set', 'এমব্রয়ডারি লিনেন সেট', 'Linen', 325000, 195000], ['Everyday block print', 'ব্লক প্রিন্ট থ্রি-পিস', 'Cotton', 165000, 90000], ['Festive silk collection', 'উৎসবের সিল্ক থ্রি-পিস', 'Silk', 485000, 290000], ['Soft muslin ensemble', 'মসলিন থ্রি-পিস', 'Muslin', 395000, 225000]];
                for ($i = 0; $i < $productCount; $i++) {
                    [$en,$bnName,$fabric,$price,$cost] = $designs[$i % 6];
                    $product = Product::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'name' => $en.' · '.($i + 1), 'name_bn' => $bnName.' · '.($i + 1), 'slug' => Str::slug($en).'-'.($i + 1), 'sku' => strtoupper($slug).'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT), 'form' => ['ready_made', 'unstitched', 'custom_stitch'][$i % 3], 'fabric' => $fabric, 'color' => ['Terracotta', 'Forest', 'Mustard', 'Indigo', 'Rose', 'Sand'][$i % 6], 'size' => ['S', 'M', 'L', 'XL', 'Free size'][$i % 5], 'price' => $price, 'cost' => $cost, 'stock' => 300, 'image' => 'img/fabric-'.($i % 6).'.svg', 'description' => 'A fictional demo design with an original illustrated swatch. Kameez, bottom and orna. Gentle hand wash recommended.']);
                    StockMovement::withoutGlobalScope('shop')->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => 300, 'reason' => 'Demo opening stock']);
                    $products[] = $product;
                }
                for ($i = 0; $i < $orderCount; $i++) {
                    $product = $products[$i % count($products)];
                    $details = ['name' => ['নুসরাত জাহান', 'সাদিয়া আক্তার', 'তাসনিম রহমান', 'মেহজাবিন ইসলাম', 'ফারহানা হক'][$i % 5].' '.(1 + $i % 30), 'phone' => '0170000'.str_pad((string) ($i % 40), 4, '0', STR_PAD_LEFT), 'address' => 'নমুনা বাড়ি '.(1 + $i % 30).', কাল্পনিক সড়ক, বাংলাদেশ', 'district' => ['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi'][$i % 4], 'cash_amount' => $product->price, 'bkash_amount' => 0];
                    $pos = $shop->enabled('pos') && $i % 4 === 0;
                    $order = $sales->create($shop, [$product->id => 1], $details, $pos ? 'pos' : 'storefront', Str::uuid()->toString());
                    if (! $pos && $i % 5 !== 0) {
                        foreach (['confirmed', 'packed', 'shipped', 'delivered'] as $status) {
                            $sales->transition($shop, $order, $status);
                        }
                        $sales->pay($shop, $order, $order->total, $i % 2 ? 'bkash' : 'cash', Str::uuid()->toString());
                    }
                    $date = now()->subDays($i % 90)->setTime(10 + ($i % 9), $i % 60);
                    foreach (['orders', 'order_lines', 'stock_movements', 'payments', 'journal_lines', 'order_events'] as $table) {
                        DB::table($table)->when($table === 'orders', fn ($q) => $q->where('id', $order->id), fn ($q) => $q->where('order_id', $order->id))->update(['created_at' => $date, 'updated_at' => $date]);
                    }
                }
            });
        }
    }
}
