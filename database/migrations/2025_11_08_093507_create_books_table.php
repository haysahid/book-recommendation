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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('translated_title')->nullable();
            $table->string('cleaned_title')->nullable();
            $table->string('stemmed_title')->nullable();
            $table->string('image')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('author')->nullable();
            $table->integer('final_price')->nullable();
            $table->integer('slice_price')->nullable();
            $table->integer('discount')->nullable();
            $table->boolean('is_oos')->nullable();
            $table->string('sku')->nullable();
            $table->string('format')->nullable();
            $table->string('store_name')->nullable();
            $table->string('isbn')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
