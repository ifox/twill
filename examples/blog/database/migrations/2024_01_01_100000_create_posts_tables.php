<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->foreignId('author_id')->nullable()->constrained('authors');
            $table->timestamp('publish_start_date')->nullable();
        });

        Schema::create('post_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'post');
            $table->string('title', 200)->nullable();
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('post_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'post');
        });

        Schema::create('post_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'post');
        });

        Schema::create('category_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->integer('position')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_post');
        Schema::dropIfExists('post_revisions');
        Schema::dropIfExists('post_slugs');
        Schema::dropIfExists('post_translations');
        Schema::dropIfExists('posts');
    }
};
