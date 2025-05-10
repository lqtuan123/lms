<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hoc_phans', function (Blueprint $table) {
            $table->boolean('is_condition_course')->default(0); // Thêm trường is_condition_course, mặc định là 0 (không phải học phần điều kiện)
        });
    }

    public function down()
    {
        Schema::table('hoc_phans', function (Blueprint $table) {
            $table->dropColumn('is_condition_course'); // Xóa trường nếu rollback
        });
    }
};
