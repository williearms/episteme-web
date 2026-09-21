<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('file_type')->nullable(); // PDF | ZIP | Template | Dataset | E-book | Other
            $table->string('file_size')->nullable(); // human-readable, e.g. "12 MB"
            $table->string('access_type')->nullable(); // e.g. "Lifetime download"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_products');
    }
};
