<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use BelongsToShop;

    protected $guarded = [];
}
