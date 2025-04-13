<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tu_luan_submissions', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('quiz_id');
            $table->json('answers'); // Lưu {question_id: int, content: string}
            $table->timestamp('submitted_at')->useCurrent();
            $table->float('score')->nullable(); // Điểm số, giáo viên chấm sau

            // Khóa ngoại
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('quiz_id')->references('id')->on('bode_tuluans')->onDelete('cascade');

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('tu_luan_submissions');
    }
};

