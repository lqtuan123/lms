<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Book\Models\Book;
use App\Modules\Book\Models\BookType;
use App\Modules\Resource\Models\Resource;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Faker\Factory as Faker;

class BookSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('vi_VN');

        // Đảm bảo có ít nhất 10 loại sách
        $bookTypeIds = BookType::pluck('id')->toArray();
        
        if (count($bookTypeIds) < 10) {
            $this->command->info('Vui lòng chạy BookTypeSeeder trước khi chạy BookSeeder.');
            return;
        }

        // Tạo danh sách tag phổ biến
        $tags = [
            'Laravel', 'PHP', 'Python', 'Java', 'JavaScript', 
            'Web Development', 'Database', 'Machine Learning', 'AI', 
            'Cloud Computing', 'Data Science', 'DevOps', 'Front-end', 
            'Back-end', 'Security', 'Mobile App', 'React', 'Vue.js', 
            'Angular', 'Node.js', 'MongoDB', 'MySQL', 'PostgreSQL',
            'AWS', 'Docker', 'Kubernetes', 'Microservices', 'RESTful API',
            'Android', 'iOS', 'Git', 'Linux', 'Windows', 'Network'
        ];

        foreach ($tags as $tagName) {
            Tag::firstOrCreate(
                ['title' => $tagName],
                [
                    'slug' => Str::slug($tagName),
                    'status' => 'active'
                ]
            );
        }

        $tagIds = Tag::whereIn('title', $tags)->pluck('id')->toArray();

        // Lấy danh sách các file PDF từ thư mục book seeder
        $pdfFiles = [];
        $pdfDirectory = public_path('book seeder/file');
        
        if (File::exists($pdfDirectory)) {
            $files = File::files($pdfDirectory);
            foreach ($files as $file) {
                if (strtolower($file->getExtension()) === 'pdf') {
                    $pdfFiles[] = [
                        'name' => $file->getFilename(),
                        'path' => 'book seeder/file/' . $file->getFilename(),
                        'size' => $file->getSize()
                    ];
                }
            }
        }

        if (empty($pdfFiles)) {
            $this->command->info('Không tìm thấy file PDF trong thư mục book seeder/file.');
            // Tạo một số file mẫu nếu không tìm thấy
            $pdfFiles = [
                ['name' => 'sample-1.pdf', 'path' => 'samples/sample-1.pdf', 'size' => 1500000],
                ['name' => 'sample-2.pdf', 'path' => 'samples/sample-2.pdf', 'size' => 2000000],
                ['name' => 'sample-3.pdf', 'path' => 'samples/sample-3.pdf', 'size' => 2500000],
            ];
        }

        // Danh sách hình ảnh đảm bảo không bị chết link
        $bookImages = [
            'https://picsum.photos/seed/php/300/400',             // PHP book
            'https://picsum.photos/seed/java/300/400',            // Java book
            'https://picsum.photos/seed/python/300/400',          // Python book
            'https://picsum.photos/seed/webdev/300/400',          // Web Development
            'https://picsum.photos/seed/database/300/400',        // Database book
            'https://picsum.photos/seed/ai/300/400',              // AI/ML book
            'https://picsum.photos/seed/cloud/300/400',           // Cloud Computing
            'https://picsum.photos/seed/security/300/400',        // Security book
            'https://picsum.photos/seed/javascript/300/400',      // JavaScript book
            'https://picsum.photos/seed/devops/300/400',          // DevOps book
            'https://picsum.photos/seed/linux/300/400',           // Linux book
            'https://picsum.photos/seed/mobile/300/400',          // Mobile Development
            'https://picsum.photos/seed/datascience/300/400',     // Data Science
            'https://picsum.photos/seed/networking/300/400',      // Networking
            'https://picsum.photos/seed/gamedev/300/400',         // Game Development
        ];

        // Danh sách 50 cuốn sách bằng tiếng Việt với hình ảnh liên quan
        $bookInfo = [
            [
                'title' => 'Lập Trình PHP Cơ Bản Và Nâng Cao',
                'summary' => 'Sách hướng dẫn từ cơ bản đến nâng cao về ngôn ngữ lập trình PHP, cùng với các framework phổ biến.',
                'tags' => ['PHP', 'Laravel', 'Web Development']
            ],
            [
                'title' => 'Lập Trình Hướng Đối Tượng Với Java',
                'summary' => 'Cuốn sách giúp bạn tiếp cận với khái niệm lập trình hướng đối tượng thông qua ngôn ngữ Java.',
                'tags' => ['Java', 'OOP']
            ],
            [
                'title' => 'Python Cho Người Mới Bắt Đầu',
                'summary' => 'Học Python từ con số 0 với các bài tập thực hành và ví dụ cụ thể.',
                'tags' => ['Python', 'Data Science']
            ],
            [
                'title' => 'Machine Learning Cơ Bản',
                'summary' => 'Tổng quan về các thuật toán Machine Learning và ứng dụng trong thực tiễn bằng Python.',
                'tags' => ['Machine Learning', 'AI', 'Python']
            ],
            [
                'title' => 'Nhập Môn Lập Trình NodeJS',
                'summary' => 'Khám phá NodeJS để phát triển ứng dụng web hiệu năng cao với JavaScript.',
                'tags' => ['Node.js', 'JavaScript', 'Web Development']
            ],
            [
                'title' => 'Lập Trình Di Động Với React Native',
                'summary' => 'Hướng dẫn lập trình ứng dụng di động đa nền tảng với React Native.',
                'tags' => ['React', 'Mobile App', 'JavaScript']
            ],
            [
                'title' => 'Làm Chủ Docker Trong Thực Tế',
                'summary' => 'Kiến thức thực tế về Docker, container và ứng dụng trong quy trình CI/CD.',
                'tags' => ['Docker', 'DevOps', 'Microservices']
            ],
            [
                'title' => 'Blockchain Và Ứng Dụng',
                'summary' => 'Tìm hiểu về công nghệ blockchain và các ứng dụng trong thực tế.',
                'tags' => ['Blockchain', 'Security', 'Cryptocurrency']
            ],
            [
                'title' => 'Thiết Kế Cơ Sở Dữ Liệu Quan Hệ',
                'summary' => 'Phương pháp thiết kế cơ sở dữ liệu quan hệ hiệu quả và tối ưu.',
                'tags' => ['Database', 'MySQL', 'PostgreSQL']
            ],
            [
                'title' => 'Linux System Programming',
                'summary' => 'Lập trình hệ thống trên Linux với các ví dụ thực tế và hướng dẫn chi tiết.',
                'tags' => ['Linux', 'System Programming', 'C']
            ],
            [
                'title' => 'Khám Phá Dữ Liệu Lớn Với Hadoop và Spark',
                'summary' => 'Xử lý dữ liệu lớn hiệu quả với các công cụ Hadoop và Apache Spark.',
                'tags' => ['Big Data', 'Data Science', 'Apache Spark']
            ],
            [
                'title' => 'Bảo Mật Ứng Dụng Web Hiện Đại',
                'summary' => 'Các kỹ thuật và công cụ để bảo vệ ứng dụng web khỏi các cuộc tấn công phổ biến.',
                'tags' => ['Web Development', 'Security', 'Cybersecurity']
            ],
            [
                'title' => 'React.js Từ A đến Z',
                'summary' => 'Hướng dẫn toàn diện về React.js từ cơ bản đến nâng cao, kèm theo các dự án thực tế.',
                'tags' => ['React', 'JavaScript', 'Front-end']
            ],
            [
                'title' => 'Trí Tuệ Nhân Tạo: Nguyên Lý và Thực Hành',
                'summary' => 'Tổng quan về trí tuệ nhân tạo, từ các nguyên lý cơ bản đến ứng dụng thực tế.',
                'tags' => ['AI', 'Machine Learning', 'Neural Networks']
            ],
            [
                'title' => 'Kiến Trúc Microservices',
                'summary' => 'Thiết kế và triển khai kiến trúc microservices cho hệ thống phần mềm phức tạp.',
                'tags' => ['Microservices', 'DevOps', 'System Architecture']
            ],
            [
                'title' => 'Xây Dựng API RESTful Với Laravel',
                'summary' => 'Phát triển API RESTful tuân thủ chuẩn với Laravel framework.',
                'tags' => ['Laravel', 'PHP', 'RESTful API']
            ],
            [
                'title' => 'Vue.js: Lập Trình Framework JavaScript Hiện Đại',
                'summary' => 'Học Vue.js từ cơ bản đến nâng cao với các ví dụ thực tế.',
                'tags' => ['Vue.js', 'JavaScript', 'Front-end']
            ],
            [
                'title' => 'MongoDB Toàn Tập',
                'summary' => 'Hướng dẫn chi tiết về MongoDB, từ cài đặt đến tối ưu hiệu suất.',
                'tags' => ['MongoDB', 'NoSQL', 'Database']
            ],
            [
                'title' => 'Làm Chủ AWS Cloud',
                'summary' => 'Triển khai và quản lý ứng dụng trên nền tảng AWS Cloud.',
                'tags' => ['AWS', 'Cloud Computing', 'DevOps']
            ],
            [
                'title' => 'TypeScript và Angular: Xây Dựng Ứng Dụng Hiện Đại',
                'summary' => 'Học cách phát triển ứng dụng web với TypeScript và Angular framework.',
                'tags' => ['Angular', 'TypeScript', 'Front-end']
            ],
            [
                'title' => 'Lập Trình Game Với Unity',
                'summary' => 'Phát triển game 2D và 3D với Unity engine và C#.',
                'tags' => ['Game Development', 'Unity', 'C#']
            ],
            [
                'title' => 'Quản Lý Dự Án Phần Mềm Agile',
                'summary' => 'Áp dụng phương pháp Agile vào quản lý dự án phần mềm hiệu quả.',
                'tags' => ['Agile', 'Project Management', 'Scrum']
            ],
            [
                'title' => 'Kubernetes Trong Thực Tiễn',
                'summary' => 'Triển khai và quản lý ứng dụng container với Kubernetes.',
                'tags' => ['Kubernetes', 'Docker', 'DevOps']
            ],
            [
                'title' => 'SQL Toàn Tập',
                'summary' => 'Từ cơ bản đến nâng cao về ngôn ngữ truy vấn SQL và tối ưu hiệu suất.',
                'tags' => ['SQL', 'Database', 'MySQL']
            ],
            [
                'title' => 'Flask: Phát Triển Web Với Python',
                'summary' => 'Xây dựng ứng dụng web với micro-framework Flask của Python.',
                'tags' => ['Python', 'Flask', 'Web Development']
            ],
            [
                'title' => 'Elasticsearch Trong Phân Tích Dữ Liệu',
                'summary' => 'Sử dụng Elasticsearch để xây dựng hệ thống tìm kiếm và phân tích dữ liệu.',
                'tags' => ['Elasticsearch', 'Big Data', 'Data Analysis']
            ],
            [
                'title' => 'Deep Learning Cơ Bản và Nâng Cao',
                'summary' => 'Các mô hình deep learning và ứng dụng trong xử lý ngôn ngữ tự nhiên, thị giác máy tính.',
                'tags' => ['Deep Learning', 'AI', 'Neural Networks']
            ],
            [
                'title' => 'ASP.NET Core MVC: Xây Dựng Ứng Dụng Web',
                'summary' => 'Phát triển ứng dụng web hiện đại với ASP.NET Core MVC và C#.',
                'tags' => ['ASP.NET', 'C#', 'Web Development']
            ],
            [
                'title' => 'Git và GitHub Cho Lập Trình Viên',
                'summary' => 'Làm chủ Git và GitHub trong quản lý mã nguồn và làm việc nhóm.',
                'tags' => ['Git', 'GitHub', 'Version Control']
            ],
            [
                'title' => 'Lập Trình Nhúng Với Arduino',
                'summary' => 'Phát triển các dự án IoT với Arduino từ cơ bản đến nâng cao.',
                'tags' => ['Arduino', 'IoT', 'Embedded Systems']
            ],
            [
                'title' => 'Lập Trình Android Nâng Cao',
                'summary' => 'Các kỹ thuật và mẫu thiết kế nâng cao trong phát triển ứng dụng Android.',
                'tags' => ['Android', 'Mobile App', 'Java']
            ],
            [
                'title' => 'iOS Development Với Swift',
                'summary' => 'Phát triển ứng dụng iOS hiện đại với ngôn ngữ Swift.',
                'tags' => ['iOS', 'Swift', 'Mobile App']
            ],
            [
                'title' => 'Lập Trình Hệ Thống Với Rust',
                'summary' => 'Học ngôn ngữ Rust để phát triển phần mềm hệ thống an toàn và hiệu quả.',
                'tags' => ['Rust', 'System Programming', 'Performance']
            ],
            [
                'title' => 'GraphQL: API Cho Thời Đại Mới',
                'summary' => 'Thiết kế và triển khai API GraphQL cho ứng dụng web và mobile.',
                'tags' => ['GraphQL', 'API', 'Web Development']
            ],
            [
                'title' => 'Kiến Trúc Phần Mềm Hướng Dịch Vụ',
                'summary' => 'Thiết kế và triển khai kiến trúc phần mềm hướng dịch vụ (SOA).',
                'tags' => ['SOA', 'System Architecture', 'Enterprise']
            ],
            [
                'title' => 'DevSecOps: Tích Hợp Bảo Mật Vào CI/CD',
                'summary' => 'Phương pháp tích hợp bảo mật vào quy trình CI/CD trong phát triển phần mềm.',
                'tags' => ['DevSecOps', 'Security', 'CI/CD']
            ],
            [
                'title' => 'Lập Trình Đa Luồng và Bất Đồng Bộ',
                'summary' => 'Kỹ thuật lập trình đa luồng và bất đồng bộ trong các ngôn ngữ hiện đại.',
                'tags' => ['Multithreading', 'Async', 'Performance']
            ],
            [
                'title' => 'Phân Tích Dữ Liệu Với Python',
                'summary' => 'Sử dụng Python và các thư viện như Pandas, NumPy để phân tích dữ liệu.',
                'tags' => ['Python', 'Data Analysis', 'Pandas']
            ],
            [
                'title' => 'Xây Dựng Chatbot Thông Minh',
                'summary' => 'Phát triển chatbot thông minh với NLP và machine learning.',
                'tags' => ['Chatbot', 'NLP', 'AI']
            ],
            [
                'title' => 'Kiểm Thử Phần Mềm Tự Động',
                'summary' => 'Phương pháp và công cụ kiểm thử tự động cho phần mềm hiện đại.',
                'tags' => ['Testing', 'Automation', 'CI/CD']
            ],
            [
                'title' => 'Lập Trình Blockchain Với Solidity',
                'summary' => 'Phát triển smart contracts và ứng dụng DApp với Solidity và Ethereum.',
                'tags' => ['Blockchain', 'Solidity', 'Ethereum']
            ],
            [
                'title' => 'Thiết Kế UX/UI Cho Lập Trình Viên',
                'summary' => 'Nguyên tắc thiết kế UX/UI cơ bản dành cho lập trình viên front-end.',
                'tags' => ['UX/UI', 'Front-end', 'Design']
            ],
            [
                'title' => 'Xử Lý Ảnh Với OpenCV và Python',
                'summary' => 'Học xử lý ảnh và thị giác máy tính với thư viện OpenCV và Python.',
                'tags' => ['OpenCV', 'Python', 'Computer Vision']
            ],
            [
                'title' => 'Lập Trình Mạng Với Go',
                'summary' => 'Phát triển ứng dụng mạng hiệu năng cao với ngôn ngữ Go.',
                'tags' => ['Go', 'Networking', 'Performance']
            ],
            [
                'title' => 'Tâm Lý Học Trong Thiết Kế Phần Mềm',
                'summary' => 'Ứng dụng các nguyên tắc tâm lý học trong thiết kế và phát triển phần mềm.',
                'tags' => ['UX', 'Psychology', 'Design']
            ],
            [
                'title' => 'Lập Trình Hàm Với JavaScript',
                'summary' => 'Áp dụng lập trình hàm (functional programming) trong JavaScript hiện đại.',
                'tags' => ['JavaScript', 'Functional Programming', 'ES6']
            ],
            [
                'title' => 'Quản Lý Cơ Sở Dữ Liệu PostgreSQL',
                'summary' => 'Cài đặt, cấu hình và quản lý hiệu quả cơ sở dữ liệu PostgreSQL.',
                'tags' => ['PostgreSQL', 'Database', 'Administration']
            ],
            [
                'title' => 'Phát Triển Ứng Dụng Với Next.js',
                'summary' => 'Xây dựng ứng dụng React hiện đại với framework Next.js.',
                'tags' => ['Next.js', 'React', 'JavaScript']
            ]
        ];

        // Lặp qua từng cuốn sách để tạo
        foreach ($bookInfo as $index => $info) {
            $title = $info['title'];
            $slug = Str::slug($title);
            $originalSlug = $slug;
            $counter = 1;

            // Đảm bảo slug không trùng lặp
            while (Book::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            // Tạo nội dung sách với Faker
            $paragraphs = $faker->paragraphs(rand(12, 25), true);
            $content = "<h2>Giới thiệu</h2>\n\n<p>{$info['summary']}</p>\n\n";
            $content .= "<h2>Nội dung chính</h2>\n\n<p>{$paragraphs}</p>\n\n";
            $content .= "<h2>Kết luận</h2>\n\n<p>" . $faker->paragraph(rand(3, 5)) . "</p>";

            // Chọn hình ảnh ngẫu nhiên từ danh sách đảm bảo
            $image = $faker->randomElement($bookImages);

            // Tạo tài nguyên cho sách (PDF)
            $resourceIds = [];
            
            // Tạo random 1-2 resources cho mỗi sách
            for ($i = 0; $i < rand(1, 2); $i++) {
                $resourceTitle = "Tài liệu - " . $title . " (Phần " . ($i + 1) . ")";
                $resourceSlug = Str::slug($resourceTitle);
                
                // Đảm bảo slug không trùng lặp
                $originalResourceSlug = $resourceSlug;
                $resourceCounter = 1;
                while (Resource::where('slug', $resourceSlug)->exists()) {
                    $resourceSlug = $originalResourceSlug . '-' . $resourceCounter++;
                }
                
                // Chọn một file PDF ngẫu nhiên từ danh sách
                $randomPdf = $faker->randomElement($pdfFiles);
                
                $resource = Resource::create([
                    'title' => $resourceTitle,
                    'slug' => $resourceSlug,
                    'file_name' => $randomPdf['name'],
                    'file_type' => 'application/pdf',
                    'file_size' => $randomPdf['size'],
                    'url' => $randomPdf['path'],
                    'type_code' => 'pdf',
                    'link_code' => null,
                    'code' => Str::random(10)
                ]);
                
                $resourceIds[] = $resource->id;
            }

            // Tạo sách
            $book = Book::create([
                'title' => $title,
                'slug' => $slug,
                'photo' => $image,
                'summary' => $info['summary'],
                'content' => $content,
                'status' => 'active',
                'user_id' => rand(1, 20), // Gắn với một trong 20 users
                'book_type_id' => $faker->randomElement($bookTypeIds),
                'block' => 'no',
                'views' => $faker->numberBetween(10, 2000),
                'resources' => json_encode([
                    'book_id' => null, // Sẽ cập nhật sau khi tạo
                    'resource_ids' => $resourceIds,
                ])
            ]);
            
            // Cập nhật book_id trong resources
            $resourcesData = json_decode($book->resources, true);
            $resourcesData['book_id'] = $book->id;
            $book->resources = json_encode($resourcesData);
            $book->save();

            // Thêm tags cho sách
            $selectedTagTitles = $info['tags'] ?? $faker->randomElements($tags, rand(2, 5));
            $selectedTagIds = Tag::whereIn('title', $selectedTagTitles)->pluck('id')->toArray();

            foreach ($selectedTagIds as $tagId) {
                DB::table('tag_books')->insert([
                    'book_id' => $book->id,
                    'tag_id' => $tagId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info("Đã tạo sách: {$title}");
        }

        $this->command->info('Đã tạo xong ' . count($bookInfo) . ' cuốn sách với tài nguyên.');
    }
}

