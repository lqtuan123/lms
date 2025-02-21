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
        Schema::create('t_tags', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique(); // Tên tag, không trùng lặp
            $table->string('slug')->unique(); // Tên tag, không trùng lặp
            $table->integer('hit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_tags');
    }
};
