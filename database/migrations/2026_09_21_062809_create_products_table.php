<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description');
            $table->text('description');
            $table->string('product_type');
            $table->string('level')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->string('thumbnail')->nullable();
            $table->string('status')->default('published');
            $table->boolean('featured')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('product_type');
            $table->index('status');
            $table->index('featured');
            $table->index(['status', 'featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
