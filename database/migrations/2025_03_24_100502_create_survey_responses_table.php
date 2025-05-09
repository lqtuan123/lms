<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('hocphan_id');
            $table->unsignedBigInteger('giangvien_id');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('survey_id')->references('id')->on('surveys')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('hocphan_id')->references('id')->on('hoc_phans')->onDelete('cascade');
            $table->foreign('giangvien_id')->references('id')->on('teacher')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_responses');
    }
};
