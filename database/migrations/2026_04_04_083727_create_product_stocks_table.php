<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductStocksTable extends Migration
{
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(0);
            $table->string('type')->default('adjustment');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('product_services')->onDelete('cascade');
            $table->index('product_id');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
}