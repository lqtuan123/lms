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
        Schema::create('file_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('file_path'); // Đường dẫn file trên server
            $table->string('download_token')->unique(); // Token duy nhất để tải
            $table->boolean('is_downloaded')->default(false); // Trạng thái đã tải chưa
            $table->timestamp('expires_at'); // Ngày hết hạn link tải
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_downloads');
    }
};
