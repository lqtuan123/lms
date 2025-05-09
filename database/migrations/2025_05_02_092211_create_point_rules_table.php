<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->integer('point_value')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Thêm dữ liệu mặc định
        DB::table('point_rules')->insert([
            ['name' => 'Đọc sách', 'code' => 'read_book', 'description' => 'Điểm khi đọc sách', 'point_value' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Đăng sách', 'code' => 'upload_book', 'description' => 'Điểm khi đăng sách mới', 'point_value' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Đọc bài viết', 'code' => 'read_blog', 'description' => 'Điểm khi đọc bài viết', 'point_value' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Đăng bài viết', 'code' => 'create_blog', 'description' => 'Điểm khi đăng bài viết mới', 'point_value' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bình luận', 'code' => 'create_comment', 'description' => 'Điểm khi bình luận', 'point_value' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Like', 'code' => 'create_like', 'description' => 'Điểm khi thích nội dung', 'point_value' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};
