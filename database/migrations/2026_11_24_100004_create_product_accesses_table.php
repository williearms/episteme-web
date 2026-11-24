<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Join links and attendee instructions for live products. Deliberately a
        // separate table from the subtype tables: those are publicly readable
        // (Phase 3 rule), this one is only ever shown to enrolled users.
        Schema::create('product_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('join_url')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_accesses');
    }
};
