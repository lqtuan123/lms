<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPointToTUserpagesTable extends Migration
{
    public function up()
    {
        Schema::table('t_userpages', function (Blueprint $table) {
            $table->integer('point')->default(0); // Thêm cột `point` với giá trị mặc định là 0
        });
    }

    public function down()
    {
        Schema::table('t_userpages', function (Blueprint $table) {
            $table->dropColumn('point');
        });
    }
}

