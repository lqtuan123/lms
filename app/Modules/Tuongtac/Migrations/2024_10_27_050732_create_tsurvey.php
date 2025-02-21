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
        Schema::create('t_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên nhóm thăm dò
            $table->string('slug'); // Tên nhóm thăm dò
            $table->integer('item_id'); // Tên nhóm thăm dò
            $table->string('item_code'); // Tên nhóm thăm dò
            $table->date('expired_date'); // Tên nhóm thăm dò
            $table->integer('user_id') ; 
            $table->json('user_ids') ; 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_surveys');
    }
};
