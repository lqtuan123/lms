<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Tuongtac\Models\TBlog;
use App\Modules\Tuongtac\Models\TTag;
use App\Modules\Group\Models\Group;
use App\Models\User;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class TBlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('vi_VN');
        
        // Lấy danh sách user_id từ bảng users
        $userIds = User::pluck('id')->toArray();
        
        // Lấy danh sách group_id từ bảng groups
        $groupIds = Group::pluck('id')->toArray();
        
        if (empty($userIds)) {
            $this->command->error('Không có người dùng nào trong hệ thống. Vui lòng chạy UserSeeder trước.');
            return;
        }
        
        if (empty($groupIds)) {
            $this->command->error('Không có nhóm nào trong hệ thống. Vui lòng chạy GroupSeeder trước.');
            return;
        }
        
        // Tạo một số tags cho blog
        $tags = [
            'Tin tức', 'Chia sẻ', 'Hỏi đáp', 'Thảo luận', 'Kinh nghiệm',
            'Tài liệu', 'Sự kiện', 'Dự án', 'Công nghệ', 'Học tập',
            'Giáo dục', 'Sức khỏe', 'Thể thao', 'Âm nhạc', 'Nghệ thuật',
            'Du lịch', 'Ẩm thực', 'Khoa học', 'Kinh doanh', 'Lập trình'
        ];
        
        foreach ($tags as $tagName) {
            TTag::firstOrCreate(
                ['title' => $tagName],
                [
                    'slug' => Str::slug($tagName),
                    'hit' => $faker->numberBetween(0, 100)
                ]
            );
        }
        
        $tagIds = TTag::whereIn('title', $tags)->pluck('id')->toArray();
        
        // Danh sách hình ảnh đảm bảo không bị chết link
        $blogImages = [
            'https://images.unsplash.com/photo-1581091870622-2c1f1f1f1f1f', // Mã code trên màn hình
            'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg', // Lập trình viên làm việc
            'https://cdn.pixabay.com/photo/2017/02/19/15/28/technology-2082642_1280.jpg', // Bảng mạch điện tử
            'https://images.unsplash.com/photo-1518770660439-4636190af475', // Mạng lưới dữ liệu
            'https://images.pexels.com/photos/3861958/pexels-photo-3861958.jpeg', // Thiết bị công nghệ
            'https://cdn.pixabay.com/photo/2016/11/29/05/15/coding-1869236_1280.jpg', // Lập trình viên gõ code
            'https://images.unsplash.com/photo-1519389950473-47ba0277781c', // Màn hình máy tính với mã code
            'https://images.pexels.com/photos/3861972/pexels-photo-3861972.jpeg', // Phòng máy chủ
            'https://cdn.pixabay.com/photo/2017/01/06/19/15/keyboard-1952014_1280.jpg', // Bàn phím máy tính
            'https://images.unsplash.com/photo-1526378722781-5b1d1a1a1a1a', // Hệ thống mạng
            'https://images.pexels.com/photos/3861973/pexels-photo-3861973.jpeg', // Thiết bị lưu trữ dữ liệu
            'https://cdn.pixabay.com/photo/2016/11/29/05/15/programming-1869236_1280.jpg', // Mã code trên màn hình
            'https://images.unsplash.com/photo-1517430816045-df4b7de1d0b3', // Lập trình viên làm việc nhóm
            'https://images.pexels.com/photos/3861971/pexels-photo-3861971.jpeg', // Thiết bị công nghệ hiện đại
            'https://cdn.pixabay.com/photo/2017/01/06/19/15/technology-1952015_1280.jpg', // Công nghệ và dữ liệu
        ];
        
        // Tạo các tiêu đề mẫu bằng tiếng Việt
        $tieuDeMau = [
            'Cách học hiệu quả trong thời đại công nghệ số',
            'Khám phá vẻ đẹp của Việt Nam qua các điểm du lịch nổi tiếng',
            'Những kỹ năng cần thiết cho sinh viên trong thế kỷ 21',
            'Bí quyết giữ sức khỏe tốt trong mùa dịch',
            'Phát triển tư duy sáng tạo thông qua đọc sách',
            'Ứng dụng trí tuệ nhân tạo trong giáo dục hiện đại',
            'Làm thế nào để quản lý thời gian hiệu quả',
            'Những món ăn truyền thống của Việt Nam được yêu thích',
            'Phương pháp học ngoại ngữ hiệu quả nhất',
            'Văn hóa đọc và tầm quan trọng của nó trong xã hội hiện đại',
            'Bảo vệ môi trường - Trách nhiệm của mỗi người',
            'Kỹ năng giao tiếp hiệu quả trong môi trường làm việc',
            'Những xu hướng công nghệ đáng chú ý năm 2025',
            'Phát triển kỹ năng lãnh đạo cho sinh viên',
            'Vai trò của giáo dục trong phát triển bền vững'
        ];
        
        // Tạo 50 bài viết blog
        for ($i = 0; $i < 50; $i++) {
            // Tạo tiêu đề ngẫu nhiên hoặc sử dụng tiêu đề mẫu
            $title = $i < count($tieuDeMau) 
                ? $tieuDeMau[$i] 
                : 'Bài viết về ' . $faker->words(rand(3, 6), true);
                
            $slug = Str::slug($title);
            $originalSlug = $slug;
            $counter = 1;
            
            // Đảm bảo slug không trùng lặp
            while (TBlog::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            
            // Chọn user_id ngẫu nhiên
            $userId = $faker->randomElement($userIds);
            
            // Chọn group_id ngẫu nhiên (có thể null với xác suất 20%)
            $groupId = $faker->boolean(80) ? $faker->randomElement($groupIds) : null;
            
            // Chọn ảnh ngẫu nhiên từ danh sách
            $photo = $faker->randomElement($blogImages);
            // Chuyển đổi thành mảng JSON với URL được bọc trong dấu ngoặc kép
            $photoJson = json_encode([$photo]);
            
            // Tạo nội dung blog bằng tiếng Việt
            $gioiThieu = [
                "Trong bài viết này, chúng ta sẽ cùng tìm hiểu về những vấn đề liên quan đến chủ đề {$title}.",
                "Hiện nay, {$title} đang là một chủ đề được nhiều người quan tâm và thảo luận.",
                "Việc nghiên cứu về {$title} mang lại nhiều lợi ích thiết thực cho cuộc sống của chúng ta.",
                "Chúng ta không thể phủ nhận tầm quan trọng của {$title} trong xã hội hiện đại."
            ];
            
            $noiDung = [
                "Đầu tiên, chúng ta cần hiểu rõ về khái niệm và bản chất của vấn đề. {$title} không chỉ đơn thuần là một chủ đề mà còn là một phần không thể thiếu trong cuộc sống hiện đại.",
                "Có nhiều yếu tố ảnh hưởng đến {$title}. Từ góc độ cá nhân, mỗi người cần có nhận thức đúng đắn và hành động phù hợp. Từ góc độ xã hội, cần có những chính sách và định hướng rõ ràng.",
                "Nghiên cứu gần đây cho thấy, việc áp dụng những phương pháp mới trong lĩnh vực này mang lại hiệu quả rõ rệt. Tuy nhiên, chúng ta vẫn cần thời gian để đánh giá tác động lâu dài.",
                "Trong bối cảnh toàn cầu hóa, {$title} càng trở nên quan trọng. Các quốc gia đang phát triển cần học hỏi kinh nghiệm từ các nước tiên tiến để áp dụng phù hợp với điều kiện của mình.",
                "Công nghệ thông tin đóng vai trò quan trọng trong việc thúc đẩy sự phát triển của {$title}. Các ứng dụng và nền tảng trực tuyến giúp mọi người tiếp cận thông tin dễ dàng hơn.",
                "Giáo dục và đào tạo là nền tảng để phát triển {$title} một cách bền vững. Cần đầu tư nhiều hơn vào việc nâng cao nhận thức và kỹ năng cho thế hệ trẻ."
            ];
            
            $ketLuan = [
                "Tóm lại, {$title} là một chủ đề quan trọng cần được quan tâm đúng mức. Mỗi cá nhân và tổ chức đều có vai trò trong việc thúc đẩy sự phát triển tích cực.",
                "Để phát triển bền vững, chúng ta cần có cái nhìn toàn diện và hành động cụ thể đối với {$title}. Hy vọng bài viết này đã cung cấp cho bạn những thông tin hữu ích.",
                "Chúng ta đã cùng tìm hiểu về {$title} và những vấn đề liên quan. Hy vọng những thông tin này sẽ giúp ích cho bạn trong công việc và cuộc sống.",
                "Với những thông tin đã chia sẻ, hy vọng bạn đã có cái nhìn rõ hơn về {$title}. Hãy áp dụng những kiến thức này vào thực tế để đạt được hiệu quả tốt nhất."
            ];
            
            // Tạo nội dung HTML
            $content = "<h2>Giới thiệu</h2>\n\n<p>" . $faker->randomElement($gioiThieu) . "</p>\n\n";
            $content .= "<h2>Nội dung chính</h2>\n\n";
            
            // Thêm 3-5 đoạn nội dung
            $selectedNoiDung = $faker->randomElements($noiDung, rand(3, 5));
            foreach ($selectedNoiDung as $paragraph) {
                $content .= "<p>{$paragraph}</p>\n\n";
            }
            
            $content .= "<h2>Kết luận</h2>\n\n<p>" . $faker->randomElement($ketLuan) . "</p>";
            
            // Tạo blog với status là số nguyên 1 (thay vì 'active')
            $blog = TBlog::create([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'photo' => $photoJson,
                'user_id' => $userId,
                'hit' => $faker->numberBetween(0, 500),
                'status' => 1, // Sửa từ 'active' thành 1
                'resources' => json_encode([
                    'files' => [],
                    'links' => []
                ]),
                'group_id' => $groupId,
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now()
            ]);
            
            // Thêm tags cho blog (2-5 tags)
            $selectedTagIds = $faker->randomElements($tagIds, rand(2, 5));
            
            foreach ($selectedTagIds as $tagId) {
                DB::table('t_tag_items')->insert([
                    'tag_id' => $tagId,
                    'item_id' => $blog->id,
                    'item_code' => 'tblog',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $this->command->info("Đã tạo blog: {$title}");
        }
        
        $this->command->info('Đã tạo 50 blog thành công!');
    }
}
