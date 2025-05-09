<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Book\Models\BookType;
use Illuminate\Support\Str;

class BookTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách 10 loại sách với tên tiếng Việt
        $bookTypes = [
            'Sách Công nghệ thông tin',
            'Sách Lập trình',
            'Sách Khoa học máy tính',
            'Sách Mạng máy tính',
            'Sách An toàn thông tin',
            'Sách Trí tuệ nhân tạo',
            'Sách Phát triển ứng dụng di động',
            'Sách Phát triển web',
            'Sách Cơ sở dữ liệu',
            'Sách Điện toán đám mây'
        ];

        foreach ($bookTypes as $typeName) {
            BookType::create([
                'title' => $typeName,
                'slug' => Str::slug($typeName),
                'status' => 'active'
            ]);
        }
    }
} 