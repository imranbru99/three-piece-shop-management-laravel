<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use BelongsToShop;

    protected $guarded = [];
}
