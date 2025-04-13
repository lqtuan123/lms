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
        Schema::table('t_blogs', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('id');
            $table->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_blogs', function (Blueprint $table) {
            $table->dropColumn('group_id');
            $table->dropIndex(['group_id']);
        });
    }
};
