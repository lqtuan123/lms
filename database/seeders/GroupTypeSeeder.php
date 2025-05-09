<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Group\Models\GroupType;
use Illuminate\Support\Str;

class GroupTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groupTypes = [
            [
                'title' => 'Công nghệ thông tin',
                'type_code' => 'it_tech',
                'status' => 'active',
            ],
            [
                'title' => 'Giáo dục',
                'type_code' => 'education',
                'status' => 'active',
            ],
            [
                'title' => 'Kinh doanh',
                'type_code' => 'business',
                'status' => 'active',
            ],
            [
                'title' => 'Sức khỏe',
                'type_code' => 'health',
                'status' => 'active',
            ],
            [
                'title' => 'Thể thao',
                'type_code' => 'sports',
                'status' => 'active',
            ],
            [
                'title' => 'Âm nhạc',
                'type_code' => 'music',
                'status' => 'active',
            ],
            [
                'title' => 'Nghệ thuật',
                'type_code' => 'art',
                'status' => 'active',
            ],
            [
                'title' => 'Du lịch',
                'type_code' => 'travel',
                'status' => 'active',
            ],
            [
                'title' => 'Ẩm thực',
                'type_code' => 'food',
                'status' => 'active',
            ],
            [
                'title' => 'Khoa học',
                'type_code' => 'science',
                'status' => 'active',
            ],
        ];

        foreach ($groupTypes as $type) {
            GroupType::firstOrCreate(
                ['type_code' => $type['type_code']],
                $type
            );
        }

        $this->command->info('Đã tạo 10 loại hội nhóm thành công!');
    }
} 