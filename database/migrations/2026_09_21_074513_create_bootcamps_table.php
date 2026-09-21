<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bootcamps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('duration')->nullable(); // human-readable, e.g. "12 minggu"
            $table->string('schedule')->nullable(); // e.g. "Senin & Rabu, 19:00-21:00 WIB"
            $table->string('cohort')->nullable(); // e.g. "Batch 5 - November 2026"
            $table->string('delivery_mode')->default('online'); // online | offline | hybrid
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bootcamps');
    }
};
