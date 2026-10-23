<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('name_bn');
            $t->string('slug')->unique();
            $t->string('preset');
            $t->string('city');
            $t->json('modules');
            $t->boolean('is_demo')->default(false);
            $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('shop_id')->nullable()->constrained();
            $t->string('role')->default('customer');
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->string('name');
            $t->string('name_bn');
            $t->string('slug');
            $t->string('sku');
            $t->string('form');
            $t->string('fabric');
            $t->string('color');
            $t->string('size');
            $t->unsignedInteger('price');
            $t->unsignedInteger('cost');
            $t->integer('stock')->default(0);
            $t->unsignedInteger('reorder_level')->default(5);
            $t->string('image')->default('img/fabric-0.svg');
            $t->text('description')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
            $t->unique(['shop_id', 'sku']);
            $t->unique(['shop_id', 'slug']);
            $t->index(['shop_id', 'active']);
        });
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->string('name');
            $t->string('phone');
            $t->text('address');
            $t->string('district');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['shop_id', 'phone']);
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->foreignId('customer_id')->constrained();
            $t->string('number')->unique();
            $t->string('source');
            $t->string('status')->default('pending');
            $t->unsignedInteger('subtotal');
            $t->unsignedInteger('delivery_charge')->default(0);
            $t->unsignedInteger('total');
            $t->unsignedInteger('paid')->default(0);
            $t->string('payment_method')->default('cod');
            $t->uuid('idempotency_key');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['shop_id', 'idempotency_key']);
            $t->index(['shop_id', 'status']);
            $t->index(['shop_id', 'created_at']);
        });
        Schema::create('order_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->foreignId('order_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->string('name');
            $t->unsignedInteger('quantity');
            $t->unsignedInteger('unit_price');
            $t->unsignedInteger('unit_cost');
            $t->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->foreignId('order_id')->nullable()->constrained();
            $t->integer('quantity');
            $t->string('reason');
            $t->timestamps();
            $t->index(['shop_id', 'created_at']);
        });
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->foreignId('order_id')->constrained();
            $t->unsignedInteger('amount');
            $t->string('method');
            $t->string('reference')->nullable();
            $t->uuid('idempotency_key');
            $t->timestamps();
            $t->unique(['shop_id', 'idempotency_key']);
        });
        Schema::create('journal_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->foreignId('order_id')->nullable()->constrained();
            $t->string('event_key');
            $t->string('account');
            $t->unsignedInteger('debit')->default(0);
            $t->unsignedInteger('credit')->default(0);
            $t->string('description');
            $t->timestamps();
            $t->index(['shop_id', 'account']);
            $t->unique(['shop_id', 'event_key', 'account']);
        });
        Schema::create('order_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shop_id')->constrained();
            $t->foreignId('order_id')->constrained();
            $t->string('status');
            $t->string('note');
            $t->timestamps();
        });
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('shop_name')->nullable();
            $t->string('plan');
            $t->text('message');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['contact_messages', 'order_events', 'journal_lines', 'payments', 'stock_movements', 'order_lines', 'orders', 'customers', 'products'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('shop_id');
            $t->dropColumn('role');
        });
        Schema::dropIfExists('shops');
    }
};
