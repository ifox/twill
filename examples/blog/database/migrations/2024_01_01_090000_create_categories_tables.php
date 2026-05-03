<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            createDefaultTableFields($table);
        });

        Schema::create('category_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'category');
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('category_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'category');
        });

        Schema::create('category_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_revisions');
        Schema::dropIfExists('category_slugs');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
    }
};
