<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            createDefaultTableFields($table);
        });

        Schema::create('author_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'author');
            $table->string('name', 200)->nullable();
            $table->text('bio')->nullable();
        });

        Schema::create('author_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'author');
        });

        Schema::create('author_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'author');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_revisions');
        Schema::dropIfExists('author_slugs');
        Schema::dropIfExists('author_translations');
        Schema::dropIfExists('authors');
    }
};
