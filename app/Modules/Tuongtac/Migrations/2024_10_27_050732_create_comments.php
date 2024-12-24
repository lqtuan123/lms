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
        Schema::create('t_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->string('item_code');
            $table->integer('user_id');
            $table->mediumText('content');
            $table->integer('parent_id');
            $table->json('resources')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_comments');
    }
};
