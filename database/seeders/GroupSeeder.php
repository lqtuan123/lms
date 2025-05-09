<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Group\Models\Group;
use App\Modules\Group\Models\GroupType;
use App\Modules\Tuongtac\Models\TPage;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use App\Models\User;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('vi_VN');
        
        // Lấy danh sách type_code từ bảng GroupType
        $groupTypeCodes = GroupType::pluck('type_code')->toArray();
        
        // Lấy danh sách user_id từ bảng users
        $userIds = User::pluck('id')->toArray();
        
        if (empty($userIds)) {
            $this->command->error('Không có người dùng nào trong hệ thống. Vui lòng chạy UserSeeder trước.');
            return;
        }
        
        $groupAvatars = [
            'https://images.unsplash.com/photo-1503023345310-bd7c1de61c7d', // Hình ảnh trừu tượng
            'https://cdn.pixabay.com/photo/2015/04/23/22/00/tree-736885_1280.jpg', // Cây đơn dưới hoàng hôn
            'https://images.pexels.com/photos/417173/pexels-photo-417173.jpeg', // Hồ giữa rừng
            'https://images.unsplash.com/photo-1493244040629-496f6d136cc3', // Núi tuyết
            'https://cdn.pixabay.com/photo/2016/11/29/09/32/adventure-1868817_1280.jpg', // Núi đá giữa trời xanh
            'https://images.pexels.com/photos/3408744/pexels-photo-3408744.jpeg', // Rừng thông trong sương
            'https://images.unsplash.com/photo-1521295121783-8a321d551ad2', // Cánh đồng và bầu trời
            'https://cdn.pixabay.com/photo/2017/08/30/07/52/mandala-2694692_1280.jpg', // Mandala
            'https://images.pexels.com/photos/417142/pexels-photo-417142.jpeg', // Cánh đồng hoa oải hương
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb', // Rừng sớm mai
        ];
        
        
        $groupCovers = [
            'https://images.unsplash.com/photo-1518770660439-4636190af475', // Mạng lưới dữ liệu
            'https://images.pexels.com/photos/3861958/pexels-photo-3861958.jpeg', // Thiết bị công nghệ
            'https://cdn.pixabay.com/photo/2017/01/06/19/15/keyboard-1952014_1280.jpg', // Bàn phím máy tính
            'https://images.unsplash.com/photo-1526378722781-5b1d1a1a1a1a', // Hệ thống mạng
            'https://images.pexels.com/photos/3861972/pexels-photo-3861972.jpeg', // Phòng máy chủ
            'https://cdn.pixabay.com/photo/2016/11/29/05/15/programming-1869236_1280.jpg', // Mã code trên màn hình
            'https://images.unsplash.com/photo-1517430816045-df4b7de1d0b3', // Lập trình viên làm việc nhóm
            'https://images.pexels.com/photos/3861971/pexels-photo-3861971.jpeg', // Thiết bị công nghệ hiện đại
            'https://cdn.pixabay.com/photo/2017/01/06/19/15/technology-1952015_1280.jpg', // Công nghệ và dữ liệu
            'https://images.unsplash.com/photo-1519389950473-47ba0277781c', // Màn hình máy tính với mã code
        ];        
        
        // Danh sách 20 nhóm
        $groupNames = [
            'Cộng đồng lập trình viên Laravel',
            'Chia sẻ kiến thức Python',
            'Hội những người yêu thích công nghệ',
            'Sinh viên CNTT Việt Nam',
            'Giáo viên dạy Toán online',
            'Chia sẻ kinh nghiệm giảng dạy',
            'Doanh nhân trẻ Việt Nam',
            'Cộng đồng startup',
            'Chăm sóc sức khỏe mỗi ngày',
            'Yoga và đời sống',
            'CLB Bóng đá nghiệp dư',
            'Người yêu chạy bộ',
            'Âm nhạc dân tộc',
            'Hội những người yêu nhạc cổ điển',
            'Nhiếp ảnh và cuộc sống',
            'Vẽ tranh sáng tạo',
            'Du lịch khám phá Việt Nam',
            'Ẩm thực đường phố',
            'Khoa học vũ trụ',
            'Khám phá thế giới tự nhiên'
        ];
        
        // Tạo 20 nhóm
        for ($i = 0; $i < 20; $i++) {
            $title = $groupNames[$i];
            $slug = Str::slug($title);
            $originalSlug = $slug;
            $counter = 1;
            
            // Đảm bảo slug không trùng lặp
            while (Group::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            
            // Chọn type_code ngẫu nhiên
            $typeCode = $faker->randomElement($groupTypeCodes);
            
            // Chọn author_id ngẫu nhiên
            $authorId = $faker->randomElement($userIds);
            
            // Tạo danh sách members (5-15 người, bao gồm cả author)
            $memberIds = $faker->randomElements($userIds, $faker->numberBetween(5, 15));
            if (!in_array($authorId, $memberIds)) {
                $memberIds[] = $authorId;
            }
            
            // Tạo danh sách moderators (1-3 người từ danh sách members, không bao gồm author)
            $moderatorIds = [];
            $potentialModerators = array_diff($memberIds, [$authorId]);
            if (count($potentialModerators) > 0) {
                $moderatorCount = min(count($potentialModerators), $faker->numberBetween(1, 3));
                $moderatorIds = $faker->randomElements($potentialModerators, $moderatorCount);
            }
            
            // Tạo danh sách pending_members (0-5 người, không trùng với members)
            $pendingMemberIds = [];
            $potentialPendingMembers = array_diff($userIds, $memberIds);
            if (count($potentialPendingMembers) > 0) {
                $pendingCount = min(count($potentialPendingMembers), $faker->numberBetween(0, 5));
                $pendingMemberIds = $faker->randomElements($potentialPendingMembers, $pendingCount);
            }
            
            // Chọn ảnh đại diện và ảnh bìa dựa trên loại nhóm
            $typeIndex = array_search($typeCode, $groupTypeCodes);
            $photoIndex = $typeIndex % count($groupAvatars);
            $coverIndex = $typeIndex % count($groupCovers);
            
            $group = Group::create([
                'title' => $title,
                'slug' => $slug,
                'description' => $faker->paragraph(3),
                'type_code' => $typeCode,
                'author_id' => $authorId,
                'pending_members' => json_encode($pendingMemberIds),
                'members' => json_encode($memberIds),
                'moderators' => json_encode($moderatorIds),
                'status' => 'active',
                'photo' => $groupAvatars[$photoIndex],
                'cover_photo' => $groupCovers[$coverIndex],
                'is_private' => $faker->boolean(30), // 30% nhóm là riêng tư
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now()
            ]);
            
            $this->command->info("Đã tạo nhóm: {$title}");
        }
        
        $this->command->info('Đã tạo 20 nhóm thành công!');
    }

    private function createPageForGroup($group)
    {
        $slug = $group->slug;

        if (!TPage::where('slug', $slug)->exists()) {
            TPage::create([
                'item_id' => $group->id,
                'item_code' => 'group',
                'title' => $group->title,
                'slug' => $slug,
                'description' => $group->description,
                'banner' => $group->cover_photo ?? $group->photo,
                'avatar' => $group->photo,
                'status' => 'active'
            ]);
        }
    }
}
