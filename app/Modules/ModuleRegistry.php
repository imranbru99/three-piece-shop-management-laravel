<?php

namespace App\Modules;

class ModuleRegistry
{
    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        static $modules;

        return $modules ??= json_decode((string) file_get_contents(__DIR__.'/catalog.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @return array<string, mixed> */
    public function get(string $key): array
    {
        return $this->all()[$key] ?? abort(404);
    }

    /** @return array<string, array<string, mixed>> */
    public function presets(): array
    {
        return [
            'online_seller' => ['name' => ['en' => 'Online sellers', 'bn' => 'অনলাইন বিক্রেতা'], 'description' => ['en' => 'From your social inbox to a shop of your own.', 'bn' => 'সোশ্যাল ইনবক্স থেকে নিজের দোকান।'], 'modules' => ['catalog', 'orders', 'order_confirmation', 'landing_pages', 'storefront', 'courier', 'payments', 'customers', 'marketing', 'ai', 'reports']],
            'boutique_retail' => ['name' => ['en' => 'Boutiques & retailers', 'bn' => 'বুটিক ও খুচরা দোকান'], 'description' => ['en' => 'A beautiful collection deserves a smoother counter.', 'bn' => 'সুন্দর কালেকশনের জন্য সহজ বিক্রয় ব্যবস্থা।'], 'modules' => ['catalog', 'inventory', 'pos', 'orders', 'customers', 'crm', 'accounts', 'hr', 'reports', 'storefront']],
            'wholesale_importer' => ['name' => ['en' => 'Wholesalers & importers', 'bn' => 'পাইকার ও আমদানিকারক'], 'description' => ['en' => 'Manage bigger consignments with a clearer picture.', 'bn' => 'বড় চালান ও পাইকারি অর্ডার সহজে পরিচালনা।'], 'modules' => ['catalog', 'inventory', 'purchasing', 'wholesale', 'accounts', 'orders', 'customers', 'reports']],
            'manufacturer_tailor' => ['name' => ['en' => 'Makers & tailors', 'bn' => 'প্রস্তুতকারক ও দর্জি'], 'description' => ['en' => 'Keep every measurement, material and milestone together.', 'bn' => 'মাপ, উপকরণ ও কাজের ধাপ একসাথে।'], 'modules' => ['catalog', 'production', 'inventory', 'purchasing', 'orders', 'customers', 'hr', 'accounts', 'reports']],
            'omnichannel_chain' => ['name' => ['en' => 'Growing shop chains', 'bn' => 'একাধিক শাখার ব্যবসা'], 'description' => ['en' => 'One connected view across your business.', 'bn' => 'সব শাখার ব্যবসা এক নজরে।'], 'modules' => array_keys($this->all())],
        ];
    }
}
