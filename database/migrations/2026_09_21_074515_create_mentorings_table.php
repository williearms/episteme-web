<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('session_duration_minutes')->nullable();
            $table->unsignedInteger('number_of_sessions')->default(1);
            $table->string('session_type')->default('1_on_1'); // 1_on_1 | group
            $table->string('delivery_mode')->default('online'); // online | offline | hybrid
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorings');
    }
};
