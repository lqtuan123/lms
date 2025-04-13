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
        Schema::table('classes', function (Blueprint $table) {
            // Add the new columns if they don't exist
            if (!Schema::hasColumn('classes', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->nullable(); // Đảm bảo kiểu dữ liệu phù hợp
                $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade'); // Giảng viên có thể là null
            }
            
            if (!Schema::hasColumn('classes', 'nganh_id')) {
                $table->unsignedBigInteger('nganh_id')->nullable(); // Đảm bảo kiểu dữ liệu phù hợp
                $table->foreign('nganh_id')->references('id')->on('nganh')->onDelete('cascade'); // Giảng viên có thể là null
            }
            
            if (!Schema::hasColumn('classes', 'max_students')) {
                $table->integer('max_students')->default(30);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['nganh_id']);
            $table->dropColumn('teacher_id');
            $table->dropColumn('nganh_id');
            $table->dropColumn('max_students');
        });
    }
};
