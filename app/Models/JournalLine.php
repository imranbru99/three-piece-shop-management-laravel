<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    use BelongsToShop;

    protected $guarded = [];
}
