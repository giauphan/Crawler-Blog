<?php

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
        Schema::create('BlogData', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('lang')->default('vi');
            $table->string('tieuDe')->unique();
            $table->string('tomTat')->nullable();
            $table->string('urlHinh');
            $table->string('ngayDang');
            $table->longText('noiDung');
            $table->bigInteger('idLT');
            $table->integer('xem')->default(0);
            $table->string('noiBat')->default(0);
            $table->integer('anHien')->default(1);
            $table->string('tags')->nullable();
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
