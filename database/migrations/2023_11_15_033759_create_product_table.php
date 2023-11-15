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
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image_uri')->nullable();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->json('technical_specifications')->nullable();
            $table->unsignedFloat('total_rating')->default(0)->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->decimal('price', 18, 0);
            $table->decimal('special_price', 18, 0)->default(0)->nullable();
            $table->decimal('selling_price', 18, 0)->default(0)->nullable();
            $table->unsignedTinyInteger('special_price_type')->default(10)->nullable();
            $table->unsignedTinyInteger('in_stock')->default(10)->nullable();
            $table->unsignedBigInteger('quantity')->default(0)->nullable();
            $table->unsignedTinyInteger('status')->default(20)->index();
            $table->unsignedTinyInteger('popular')->default(20)->index();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('brand_id')->references('id')->on('brand')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
        });

        Schema::create('product_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedFloat('rating');
            $table->string('content');
            $table->unsignedTinyInteger('status')->default(20)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('product_attribute', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id');

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attribute')->onDelete('cascade');
            $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->onDelete('cascade');
        });

        Schema::create('product_related', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_related_id');

            $table->primary(['product_id', 'product_related_id']);
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('product_related_id')->references('id')->on('product')->onDelete('cascade');
        });

        Schema::create('product_upsell', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_upsell_id');

            $table->primary(['product_id', 'product_upsell_id']);
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('product_upsell_id')->references('id')->on('product')->onDelete('cascade');
        });

        Schema::create('product_cross_sell', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_cross_sell_id');

            $table->primary(['product_id', 'product_cross_sell_id']);
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('product_cross_sell_id')->references('id')->on('product')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
        Schema::dropIfExists('product_comments');
        Schema::dropIfExists('product_attribute');
        Schema::dropIfExists('product_related');
        Schema::dropIfExists('product_upsell');
        Schema::dropIfExists('product_cross_sell');
    }
};
