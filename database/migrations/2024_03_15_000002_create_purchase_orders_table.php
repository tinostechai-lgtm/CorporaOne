<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('supplier')->nullable();
            $table->date('order_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('items')->nullable();
            $table->double('total_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
