<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToShop
{
    protected static function bootBelongsToShop(): void
    {
        static::addGlobalScope('shop', function (Builder $query): void {
            $query->where($query->getModel()->qualifyColumn('shop_id'), auth()->check() ? auth()->user()->shop_id : 0);
        });
        static::creating(function ($model): void {
            $user = auth()->user();
            $model->shop_id ??= $user ? $user->shop_id : null;
            if (! $model->shop_id) {
                throw new \LogicException('A shop is required.');
            }
        });
    }
}
