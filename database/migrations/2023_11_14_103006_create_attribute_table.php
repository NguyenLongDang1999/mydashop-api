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
        Schema::create('attribute', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('status')->default(20)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('category_attribute', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('attribute_id');

            $table->primary(['category_id', 'attribute_id']);
            $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attribute')->onDelete('cascade');
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->unsignedInteger('attribute_id');

            $table->foreign('attribute_id')->references('id')->on('attribute')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('category_attribute');
    }
};
