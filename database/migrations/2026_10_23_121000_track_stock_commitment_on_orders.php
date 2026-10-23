<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('stock_committed')->default(false);
        });
        // Backfill only where a real stock deduction was recorded.
        DB::table('orders')->whereIn('id', function ($query) {
            $query->select('order_id')->from('stock_movements')->where('quantity', '<', 0)->whereNotNull('order_id');
        })->whereNotIn('status', ['cancelled', 'returned'])->update(['stock_committed' => true]);
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('stock_committed'));
    }
};
