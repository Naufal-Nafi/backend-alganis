<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consignments', function (Blueprint $table) {
            $table->id('consignment_id');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->unsignedBigInteger('user_id')
                    ->foreign('user_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('set null');
            $table->string('creator_name'); 
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('store_id')->on('stores');

            $table->date('entry_date')->nullable();
            $table->date('exit_date');
            $table->integer('stock');
            $table->integer('sold')->nullable();            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('consignments');
    }
};
