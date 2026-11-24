<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Lessons were plain text (paragraphs separated by blank lines) until they
     * became rich-text. Wrap the old ones in <p> so they keep their paragraphs
     * in the editor and on the page; content that already has tags is left alone.
     */
    public function up(): void
    {
        DB::table('lessons')->orderBy('id')->each(function ($lesson) {
            if ($lesson->content !== strip_tags($lesson->content)) {
                return;
            }

            $html = collect(preg_split('/\R{2,}/', trim($lesson->content)))
                ->filter()
                ->map(fn (string $paragraph) => '<p>'.nl2br(e($paragraph), false).'</p>')
                ->implode('');

            DB::table('lessons')->where('id', $lesson->id)->update(['content' => $html]);
        });
    }

    public function down(): void
    {
        // Not reversible: once authors edit lessons as HTML there's no plain-text original to restore.
    }
};
