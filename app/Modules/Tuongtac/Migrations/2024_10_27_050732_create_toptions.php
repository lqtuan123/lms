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
        Schema::create('t_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id');
            $table->string('option_text'); // Nội dung lựa chọn
            $table->integer('votes')->default(0); // Số lượng lượt chọn
            $table->json('users')->nullable(); // Số lượng lượt chọn
            $table->integer('user_id') ; 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_options');
    }
};
