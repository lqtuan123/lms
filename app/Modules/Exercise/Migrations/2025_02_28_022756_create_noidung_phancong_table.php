<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::create('noidung_phancong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phancong_id')->constrained('phancong')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->integer('time_limit')->nullable();
            $table->json('resources')->nullable();
            $table->json('tags')->nullable();
            $table->json('tuluan')->nullable();
            $table->json('tracnghiem')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noidung_phancong');
    }
};
