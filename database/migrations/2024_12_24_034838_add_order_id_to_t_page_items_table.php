<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('t_page_items', function (Blueprint $table) {
        $table->integer('order_id')->nullable();  // Thêm cột order_id, kiểu integer và có thể null
    });
}

public function down()
{
    Schema::table('t_page_items', function (Blueprint $table) {
        $table->dropColumn('order_id');  // Gỡ bỏ cột order_id nếu rollback
    });
}

};
