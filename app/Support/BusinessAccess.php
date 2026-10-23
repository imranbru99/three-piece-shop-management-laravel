<?php

namespace App\Support;

use App\Models\Shop;
use App\Models\User;

class BusinessAccess
{
    /** @return list<string> */
    public static function allowed(string $role): array
    {
        return match ($role) {
            'owner', 'manager' => ['dashboard', 'catalog', 'orders', 'customers', 'pos', 'inventory', 'payments', 'accounts', 'reports', 'modules'],
            'cashier' => ['dashboard', 'catalog', 'orders', 'customers', 'pos'],
            'agent' => ['dashboard', 'catalog', 'orders', 'customers'],
            'stock' => ['dashboard', 'catalog', 'inventory'],
            'accounts' => ['dashboard', 'orders', 'payments', 'accounts', 'reports'],
            default => [],
        };
    }

    public static function check(User $user, Shop $shop, string $module): void
    {
        abort_unless($user->shop_id === $shop->id && in_array($module, self::allowed($user->role), true), 403);
        abort_unless(in_array($module, ['dashboard', 'modules'], true) || $shop->enabled($module), 404);
    }
}
