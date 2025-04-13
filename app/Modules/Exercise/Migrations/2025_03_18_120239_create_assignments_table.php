<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('quiz_id');
            $table->enum('quiz_type', ['trac_nghiem', 'tu_luan']);
            $table->unsignedBigInteger('hocphan_id'); // Thay student_id bằng hocphan_id
            $table->foreign('hocphan_id')->references('id')->on('hoc_phans')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assignments');
    }
};
