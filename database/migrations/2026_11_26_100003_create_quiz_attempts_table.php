<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            // The result is stored, not recomputed: editing the quiz later must not change a past grade.
            $table->unsignedSmallInteger('correct_count');
            $table->unsignedSmallInteger('total_questions');
            $table->unsignedTinyInteger('percentage');
            $table->boolean('passed');
            // question id => selected option ids, kept so the result page can show what was chosen.
            $table->json('answers');
            $table->timestamps();

            $table->index(['enrollment_id', 'quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
