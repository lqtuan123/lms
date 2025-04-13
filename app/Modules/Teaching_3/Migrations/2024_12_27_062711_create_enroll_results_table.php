<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enroll_results', function (Blueprint $table) {
            $table->id(); // Primary key

            $table->unsignedBigInteger('enroll_id'); // Foreign key to enrollments table
            $table->foreign('enroll_id')->references('id')->on('enrollments')->onDelete('cascade');

            $table->unsignedBigInteger('student_id'); // Foreign key to users table
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');

            $table->decimal('DiemBP', 5, 2)->nullable(); // Grade, e.g., 95.50
            $table->decimal('Thi1', 5, 2)->nullable(); // Grade, e.g., 95.50
            $table->decimal('Diem1', 5, 2)->nullable(); // Grade, e.g., 95.50
            $table->decimal('Thi2', 5, 2)->nullable(); // Grade, e.g., 95.50
            $table->decimal('Diem2', 5, 2)->nullable(); // Grade, e.g., 95.50
            $table->decimal('DiemMax', 5, 2)->nullable(); // Grade, e.g., 95.50
            $table->string('DiemChu')->nullable();
            $table->integer('DiemHeSo4')->nullable(); // Grade, e.g., 95.50

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enroll_results');
    }
};
