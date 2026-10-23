<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @property list<string> $modules */
class Shop extends Model
{
    protected $guarded = [];

    /** @return array<string,string> */
    protected function casts(): array
    {
        return ['modules' => 'array', 'is_demo' => 'boolean'];
    }

    public function enabled(string $key): bool
    {
        return in_array($key, ['core', 'catalog', 'orders', 'customers'], true) || in_array($key, $this->modules, true);
    }
}
