<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hocphan_id');
            $table->unsignedBigInteger('giangvien_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('hocphan_id')->references('id')->on('hoc_phans')->onDelete('cascade');
            $table->foreign('giangvien_id')->references('id')->on('teacher')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('surveys');
    }
};
