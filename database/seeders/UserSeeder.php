<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        // Danh sách 20 tên Việt Nam phổ biến
        $vietnameseNames = [
            'Nguyễn Văn Tuấn', 'Trần Minh Tuấn', 'Lê Anh Tuấn', 'Phạm Quốc Tuấn', 
            'Hoàng Thanh Tuấn', 'Đỗ Minh Tuấn', 'Vũ Đình Tuấn', 'Bùi Quang Tuấn',
            'Đặng Huy Tuấn', 'Ngô Văn Tuấn', 'Trịnh Xuân Tuấn', 'Đinh Mạnh Tuấn',
            'Lý Quang Tuấn', 'Hồ Văn Tuấn', 'Dương Thế Tuấn', 'Phan Văn Tuấn',
            'Võ Đình Tuấn', 'Trương Minh Tuấn', 'Lương Thế Tuấn', 'Cao Văn Tuấn'
        ];

        // Mảng các địa chỉ Việt Nam
        $vietnameseAddresses = [
            'Số 123 Nguyễn Trãi, Thanh Xuân, Hà Nội',
            'Số 45 Lê Lợi, Quận 1, TP. Hồ Chí Minh',
            'Số 67 Nguyễn Huệ, Quận Hải Châu, Đà Nẵng',
            'Số 89 Trần Phú, TP. Nha Trang, Khánh Hòa',
            'Số 22 Lê Duẩn, TP. Huế, Thừa Thiên Huế',
            'Số 55 Nguyễn Sinh Sắc, TP. Buôn Ma Thuột, Đắk Lắk',
            'Số 102 Điện Biên Phủ, Quận Thanh Khê, Đà Nẵng',
            'Số 78 Nguyễn Đình Chiểu, Quận 3, TP. Hồ Chí Minh',
            'Số 45 Hàng Bài, Hoàn Kiếm, Hà Nội',
            'Số 36 Trường Chinh, TP. Pleiku, Gia Lai',
            'Số 29 Bà Triệu, TP. Hải Phòng',
            'Số 17 Lê Hồng Phong, TP. Vinh, Nghệ An',
            'Số 72 Quang Trung, TP. Quy Nhơn, Bình Định',
            'Số 19 Phạm Ngọc Thạch, Quận Đống Đa, Hà Nội',
            'Số 63 Nguyễn Thái Học, TP. Vũng Tàu, Bà Rịa - Vũng Tàu',
            'Số 51 Lê Thánh Tông, TP. Hạ Long, Quảng Ninh',
            'Số 84 Võ Văn Tần, Quận 3, TP. Hồ Chí Minh',
            'Số 27 Trần Hưng Đạo, TP. Cần Thơ',
            'Số 39 Nguyễn Chí Thanh, Quận Ba Đình, Hà Nội',
            'Số 92 Lê Duẩn, TP. Đà Nẵng'
        ];

        // Mảng mô tả người dùng
        $descriptions = [
            'Yêu thích đọc sách về lập trình và công nghệ thông tin',
            'Là giảng viên giảng dạy về công nghệ phần mềm tại đại học',
            'Sinh viên năm thứ 3 ngành Khoa học máy tính',
            'Nhà phát triển web với 5 năm kinh nghiệm làm việc',
            'Người đam mê học hỏi về trí tuệ nhân tạo và học máy',
            'Chuyên viên phân tích dữ liệu tại công ty công nghệ',
            'Quản trị viên hệ thống mạng với chứng chỉ CCNA',
            'Người đam mê nghiên cứu về an ninh mạng và bảo mật thông tin',
            'Sinh viên mới tốt nghiệp đang tìm hiểu về phát triển ứng dụng di động',
            'Giảng viên hướng dẫn các khóa học online về lập trình Python',
            'Chuyên gia tư vấn về chuyển đổi số cho các doanh nghiệp vừa và nhỏ',
            'Nhà phát triển game độc lập với đam mê về thiết kế đồ họa',
            'Người sáng tạo nội dung về công nghệ trên các nền tảng mạng xã hội',
            'Kỹ sư phần mềm đang nghiên cứu về Internet vạn vật (IoT)',
            'Quản lý dự án CNTT với chứng chỉ PMP và Scrum Master',
            'Sinh viên đam mê học hỏi về điện toán đám mây và DevOps',
            'Chuyên viên tối ưu hóa công cụ tìm kiếm (SEO) cho các trang web',
            'Nhà phát triển phần mềm tự do với nhiều dự án nguồn mở',
            'Giảng viên đại học với chuyên môn về cơ sở dữ liệu và hệ thống thông tin',
            'Người sáng lập startup trong lĩnh vực công nghệ giáo dục'
        ];

      
        $userAvatars = [
            'https://loremflickr.com/320/240',
            'https://loremflickr.com/640/480',
            'https://loremflickr.com/800/600',
            'https://loremflickr.com/1024/768',
            'https://picsum.photos/200/300',
            'https://picsum.photos/300/200',
            'https://picsum.photos/400/400',
            'https://picsum.photos/500/300',
            'https://picsum.photos/600/400',
        ];
               

        $userBanners = [
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb', // Rừng sớm mai
            'https://images.pexels.com/photos/417173/pexels-photo-417173.jpeg', // Hồ giữa rừng
            'https://cdn.pixabay.com/photo/2015/04/23/22/00/tree-736885_1280.jpg', // Cây cô đơn
            'https://images.unsplash.com/photo-1493244040629-496f6d136cc3', // Dãy núi tuyết
            'https://images.pexels.com/photos/417142/pexels-photo-417142.jpeg', // Đồng hoa oải hương
            'https://images.unsplash.com/photo-1521295121783-8a321d551ad2', // Cánh đồng và bầu trời
            'https://images.pexels.com/photos/3408744/pexels-photo-3408744.jpeg', // Rừng thông trong sương
            'https://cdn.pixabay.com/photo/2016/11/29/09/32/adventure-1868817_1280.jpg', // Núi đá giữa trời xanh
            'https://images.unsplash.com/photo-1501785888041-af3ef285b470', // Dòng suối và núi đá
            'https://images.pexels.com/photos/210186/pexels-photo-210186.jpeg', // Đường mòn trong rừng
        ];
        

        // Tạo 20 user
        for ($i = 0; $i < 20; $i++) {
            $fullName = $vietnameseNames[$i];
            $nameParts = explode(' ', $fullName);
            $lastName = end($nameParts);
            
            // Tạo username từ họ tên
            $username = Str::slug($lastName . rand(100, 999));
            
            // Tạo email theo định dạng tuan01 đến tuan20
            $emailNumber = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $email = "tuan{$emailNumber}@gmail.com";
            
            // Đảm bảo không trùng username và email
            while (User::where('username', $username)->exists()) {
                $username = Str::slug($lastName . rand(100, 999));
            }
            
            while (User::where('email', $email)->exists()) {
                $emailNumber = rand(21, 100);
                $email = "tuan{$emailNumber}@gmail.com";
            }
            
            // Chọn ảnh đại diện và ảnh bìa ngẫu nhiên
            $photo = $faker->randomElement($userAvatars);
            $banner = $faker->randomElement($userBanners);
            
            // Tạo user
            $user = User::create([
                'full_name' => $fullName,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make('12345678'),
                'photo' => $photo,
                'banner' => $banner,
                'phone' => '0' . $faker->numberBetween(3, 9) . $faker->randomNumber(8, true),
                'address' => $vietnameseAddresses[$i],
                'description' => $descriptions[$i],
                'ugroup_id' => $faker->numberBetween(1, 3),
                'role' => $faker->randomElement(['giangvien', 'sinhvien']),
                'totalpoint' => $faker->numberBetween(0, 5000),
                'status' => 'active',
            ]);
            
            // Cập nhật mã code
            $user->code = "USER" . sprintf('%06d', $user->id);
            $user->save();
            
            $this->command->info("Đã tạo người dùng: {$fullName} - {$email}");
        }
        
        $this->command->info('Đã tạo 20 người dùng thành công!');
    }
} 