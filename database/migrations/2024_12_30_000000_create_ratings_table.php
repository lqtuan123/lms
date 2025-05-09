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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('rating', 3, 1); // Điểm đánh giá từ 0.0 đến 5.0
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Mỗi người dùng chỉ có thể đánh giá một cuốn sách một lần
            $table->unique(['user_id', 'book_id']);
        });
        
        // Thêm cột average_rating và rating_count vào bảng books
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa cột average_rating và rating_count khỏi bảng books
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('average_rating');
            $table->dropColumn('rating_count');
        });
        
        Schema::dropIfExists('ratings');
    }
}; 