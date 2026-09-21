<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3: normalizes the Phase 2 `products` table for the finalized
 * Product & Catalog domain.
 *
 * - `currency` + `published_at` make the base product ready for Commerce.
 * - `outcomes` / `requirements` / `audience` replace the generic `meta` JSON
 *   blob with named columns for the marketing copy that's common across
 *   most product types (previously crammed into `meta` alongside
 *   type-specific fields).
 * - `duration` is dropped: it mixed unrelated units across types (weeks for
 *   courses, minutes for workshops, "60 minutes/session" for mentoring).
 *   Each subtype table now owns its own duration field in the unit that
 *   actually makes sense for that type (see the create_*_table migrations),
 *   and Product::duration_label derives a display string from whichever
 *   subtype is attached.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('currency', 3)->default('IDR')->after('price');
            $table->timestamp('published_at')->nullable()->after('status');
            $table->json('outcomes')->nullable()->after('description');
            $table->json('requirements')->nullable()->after('outcomes');
            $table->text('audience')->nullable()->after('requirements');
            $table->index('price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['duration', 'meta']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('duration')->nullable();
            $table->json('meta')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['price']);
            $table->dropColumn(['currency', 'published_at', 'outcomes', 'requirements', 'audience']);
        });
    }
};
