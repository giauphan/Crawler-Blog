<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('BlogData', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->longText('content');
            $table->string('source');
            $table->float('SimilarityPercentage');
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
