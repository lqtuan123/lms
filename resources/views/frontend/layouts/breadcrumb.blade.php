<?php
// Get current route name
$routeName = Route::currentRouteName();
$routeParams = Route::current()->parameters();

// Define breadcrumb structure
$breadcrumbs = [];

// Home is always the first item
$breadcrumbs[] = [
    'title' => 'Trang chủ',
    'url' => route('home'),
    'icon' => 'fas fa-home',
];

// Define breadcrumb structures based on route
if ($routeName) {
    // Books routes
    if (Str::startsWith($routeName, 'front.book.')) {
        $breadcrumbs[] = [
            'title' => 'Thư viện sách',
            'url' => route('front.book.index'),
        ];

        // Book detail page
        if ($routeName === 'front.book.show' && isset($routeParams['slug'])) {
            // Get the book title if available
            $book = \App\Modules\Book\Models\Book::where('slug', $routeParams['slug'])->first();
            if ($book) {
                $breadcrumbs[] = [
                    'title' => $book->title,
                    'url' => null, // Current page, no URL
                ];
            }
        }
        
        // Book type page
        if ($routeName === 'front.book.byType' && isset($routeParams['slug'])) {
            // Get the type name if available
            $bookType = \App\Modules\Book\Models\BookType::where('slug', $routeParams['slug'])->first();
            if ($bookType) {
                $breadcrumbs[] = [
                    'title' => $bookType->title,
                    'url' => null,
                ];
            } elseif ($routeParams['slug'] === 'all') {
                $breadcrumbs[] = [
                    'title' => 'Tất cả thể loại',
                    'url' => null,
                ];
            }
        }
        
        // Recent books page
        if ($routeName === 'front.book.recentBook') {
            $breadcrumbs[] = [
                'title' => 'Sách đọc gần đây',
                'url' => null,
            ];
        }

        // Search books page
        if ($routeName === 'front.book.search') {
            $breadcrumbs[] = [
                'title' => 'Tìm kiếm sách',
                'url' => null,
            ];
        }

        // Advanced search page
        if ($routeName === 'frontend.book.advanced-search') {
            $breadcrumbs[] = [
                'title' => 'Tìm kiếm nâng cao',
                'url' => null,
            ];
        }

        // Reader page
        if ($routeName === 'front.book.reader' && isset($routeParams['slug'])) {
            $book = \App\Modules\Book\Models\Book::where('slug', $routeParams['slug'])->first();
            if ($book) {
                $breadcrumbs[] = [
                    'title' => $book->title,
                    'url' => route('front.book.show', $book->slug),
                ];
                
                $breadcrumbs[] = [
                    'title' => 'Đọc sách',
                    'url' => null,
                ];
            }
        }
    }
    
    // Group routes
    elseif (Str::startsWith($routeName, 'group.')) {
        $breadcrumbs[] = [
            'title' => 'Nhóm học tập',
            'url' => route('group.index'),
        ];
        
        // Group detail page
        if ($routeName === 'group.show' && isset($routeParams['id'])) {
            $group = \App\Modules\Group\Models\Group::find($routeParams['id']);
            if ($group) {
                $breadcrumbs[] = [
                    'title' => $group->title,
                    'url' => null,
                ];
            }
        }
        
        // Group create page
        if ($routeName === 'group.create') {
            $breadcrumbs[] = [
                'title' => 'Tạo nhóm mới',
                'url' => null,
            ];
        }

        // Group edit page
        if ($routeName === 'group.edit' && isset($routeParams['id'])) {
            $group = \App\Modules\Group\Models\Group::find($routeParams['id']);
            if ($group) {
                $breadcrumbs[] = [
                    'title' => $group->title,
                    'url' => route('group.show', $group->id),
                ];
                $breadcrumbs[] = [
                    'title' => 'Chỉnh sửa nhóm',
                    'url' => null,
                ];
            }
        }
    }
    
    // Blog routes (TBlogs)
    elseif (Str::startsWith($routeName, 'front.tblogs.')) {
        $breadcrumbs[] = [
            'title' => 'Cộng đồng',
            'url' => route('front.tblogs.index'),
        ];
        
        // Blog detail page
        if ($routeName === 'front.tblogs.show' && isset($routeParams['id'])) {
            $blog = \App\Modules\Tuongtac\Models\TBlog::find($routeParams['id']);
            if ($blog) {
                $breadcrumbs[] = [
                    'title' => $blog->title,
                    'url' => null,
                ];
            }
        }
        
        // Blog create page
        if ($routeName === 'front.tblogs.create') {
            $breadcrumbs[] = [
                'title' => 'Tạo bài viết mới',
                'url' => null,
            ];
        }
        
        // Blog edit page
        if ($routeName === 'front.tblogs.edit' && isset($routeParams['id'])) {
            $blog = \App\Modules\Tuongtac\Models\TBlog::find($routeParams['id']);
            if ($blog) {
                $breadcrumbs[] = [
                    'title' => $blog->title,
                    'url' => route('front.tblogs.show', $blog->id),
                ];
                $breadcrumbs[] = [
                    'title' => 'Chỉnh sửa bài viết',
                    'url' => null,
                ];
            }
        }
    }
    
    // Leaderboard routes
    elseif ($routeName === 'front.leaderboard') {
        $breadcrumbs[] = [
            'title' => 'Bảng xếp hạng',
            'url' => null,
        ];
    }
    
    // User profile routes
    elseif (Str::startsWith($routeName, 'front.profile')) {
        $breadcrumbs[] = [
            'title' => 'Hồ sơ cá nhân',
            'url' => route('front.profile'),
        ];
        
        // Profile edit page
        if ($routeName === 'front.profile.edit') {
            $breadcrumbs[] = [
                'title' => 'Chỉnh sửa hồ sơ',
                'url' => null,
            ];
        }
    }
    
    // User books routes
    elseif (Str::startsWith($routeName, 'user.books.')) {
        $breadcrumbs[] = [
            'title' => 'Sách của tôi',
            'url' => route('user.books.index'),
        ];
        
        // Create book page
        if ($routeName === 'user.books.create') {
            $breadcrumbs[] = [
                'title' => 'Thêm sách mới',
                'url' => null,
            ];
        }
        
        // Edit book page
        if ($routeName === 'user.books.edit' && isset($routeParams['id'])) {
            $book = \App\Modules\Book\Models\Book::find($routeParams['id']);
            if ($book) {
                $breadcrumbs[] = [
                    'title' => 'Chỉnh sửa: ' . $book->title,
                    'url' => null,
                ];
            }
        }
    }
    
    // Notifications routes
    elseif ($routeName === 'notifications.index') {
        $breadcrumbs[] = [
            'title' => 'Thông báo',
            'url' => null,
        ];
    }
    
    // Contact page
    elseif ($routeName === 'contact') {
        $breadcrumbs[] = [
            'title' => 'Liên hệ',
            'url' => null,
        ];
    }
}
?>

<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3 flex-wrap">
        @foreach($breadcrumbs as $index => $breadcrumb)
            <li class="inline-flex items-center {{ $index > 0 ? 'mt-1 md:mt-0' : '' }}">
                @if($index > 0)
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                @endif
                
                @if($breadcrumb['url'])
                    <a href="{{ $breadcrumb['url'] }}" class="inline-flex items-center text-sm font-medium {{ $index === count($breadcrumbs) - 1 ? 'text-gray-500' : 'text-gray-700 hover:text-blue-600' }}">
                        @if(isset($breadcrumb['icon']))
                            <i class="{{ $breadcrumb['icon'] }} mr-2"></i>
                        @endif
                        {{ $breadcrumb['title'] }}
                    </a>
                @else
                    <span class="text-sm font-medium text-gray-500">
                        @if(isset($breadcrumb['icon']))
                            <i class="{{ $breadcrumb['icon'] }} mr-2"></i>
                        @endif
                        {{ $breadcrumb['title'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>