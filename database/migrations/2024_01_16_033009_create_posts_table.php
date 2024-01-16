<?php

use App\Models\CategoryBlog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Unique;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('lang')->default('en');
            $table->string('title')->unique();
            $table->string('summary')->nullable();
            $table->string('images');
            $table->string('published_at');
            $table->longText('content');
            $table->integer('view')->default(0);
            $table->integer('show')->default(1);
            $table->string('tags')->nullable();
            $table->float('SimilarityPercentage');
            $table->foreignIdFor(CategoryBlog::class)->index();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BlogData');
    }
};
