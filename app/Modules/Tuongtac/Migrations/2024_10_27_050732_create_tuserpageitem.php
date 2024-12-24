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
        Schema::create('t_userpage_items', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('page_id');
            $table->integer('item_id');
            $table->string('item_code');
            $table->integer('location');
            $table->enum('status',['công khai','riêng tư'])->default('công khai');
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_userpage_items');
    }
};
