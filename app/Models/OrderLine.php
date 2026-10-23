<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    use BelongsToShop;

    protected $guarded = [];
}
