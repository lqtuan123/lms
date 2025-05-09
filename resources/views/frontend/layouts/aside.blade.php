<?php
// Get 5 newest community posts
$latestPosts = \App\Modules\Tuongtac\Models\TBlog::with(['author', 'group'])
    ->where('status', 1)
    ->where(function ($query) {
        $userId = auth()->id();

        $query->where(function ($q) {
            $q->whereNull('group_id')->orWhere('group_id', 0);
        });

        $query->orWhereHas('group', function ($q) {
            $q->where('is_private', 0)->where('status', 'active');
        });

        if ($userId) {
            $query->orWhereHas('group', function ($q) use ($userId) {
                $q->where('is_private', 1)
                  ->where('status', 'active')
                  ->where(function ($subQ) use ($userId) {
                      $subQ->whereRaw("JSON_CONTAINS(members, '\"$userId\"')")
                           ->orWhereRaw("JSON_CONTAINS(moderators, '\"$userId\"')")
                           ->orWhere('author_id', $userId);
                  });
            });
        }
    })
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

// Add comment count and likes count
foreach ($latestPosts as $post) {
    $post->comment_count = \App\Modules\Tuongtac\Models\TComment::where('item_id', $post->id)
        ->where('item_code', 'tblog')
        ->where('status', 'active')
        ->count();

    $motionItem = \App\Modules\Tuongtac\Models\TMotionItem::where('item_id', $post->id)
        ->where('item_code', 'tblog')
        ->first();

    $post->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
}

// Get 5 newest comments where item_code is specifically 'tblog'
$latestComments = \App\Modules\Tuongtac\Models\TComment::with(['author', 'tblog', 'book'])
    ->where('status', 'active')
    ->whereIn('item_code', ['tblog', 'book']) // Load comments for both blogs and books
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Bổ sung thông tin likes và replies cho mỗi bình luận
foreach($latestComments as $comment) {
    // Lấy số lượt thích cho bình luận
    $comment->likes_count = \App\Models\CommentLike::where('comment_id', $comment->id)->count();
    
    // Lấy số lượng phản hồi cho bình luận
    $comment->replies_count = \App\Modules\Tuongtac\Models\TComment::where('parent_id', $comment->id)
        ->where('status', 'active')
        ->count();
}

// Get 5 most active groups (based on member count)
$activeGroups = \App\Modules\Group\Models\Group::where('status', 'active')
    ->orderByRaw('JSON_LENGTH(members) DESC')
    ->limit(10)
    ->get();

// Get tags
$tags = \App\Models\Tag::where('status', 'active')
    ->orderBy('title')
    ->limit(10)
    ->get();

// Get top leaderboard users
// Lấy top 5 người dùng có điểm cao nhất cho trang chủ
$topLeaderboardUsers = \App\Models\User::select('users.id', 'users.full_name', 'users.photo', 'users.email', \Illuminate\Support\Facades\DB::raw('SUM(point_histories.point) as total_points'))
    ->leftJoin('point_histories', 'users.id', '=', 'point_histories.user_id')
    ->where('users.status', 'active')
    ->where('point_histories.status', 'active')
    ->groupBy('users.id', 'users.full_name', 'users.photo', 'users.email')
    ->orderBy('totalpoint', 'desc')
    ->limit(5)
    ->get();
?>

<style>
    /* Sidebar styles */
    .sidebar-section {
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.03);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .sidebar-section:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .sidebar-heading {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.5rem;
    }
    
    .sidebar-heading:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 3px;
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        border-radius: 2px;
    }
    
    /* Tab styles */
    .tabs-container {
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    
    .tabs-nav {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }
    
    .tabs-nav::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }
    
    .tab-button {
        padding: 0.75rem 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: #64748b;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    .tab-button:hover {
        color: #1e293b;
    }
    
    .tab-button.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }
    
    /* Card items */
    .sidebar-card {
        display: flex;
        align-items: flex-start;
        padding: 1rem;
        border-radius: 12px;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
    }
    
    .sidebar-card:hover {
        background-color: #f8fafc;
        transform: translateX(3px);
    }
    
    .sidebar-card:last-child {
        margin-bottom: 0;
    }
    
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border: 2px solid white;
        transition: all 0.3s ease;
    }
    
    .sidebar-card:hover .avatar-circle {
        transform: scale(1.1);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }
    
    .card-content {
        flex: 1;
        margin-left: 0.75rem;
    }
    
    .card-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }
    
    .card-subtitle {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }
    
    .card-text {
        font-size: 0.875rem;
        color: #334155;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .card-meta {
        display: flex;
        align-items: center;
        font-size: 0.75rem;
        color: #64748b;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        margin-right: 1rem;
    }
    
    .meta-item i {
        margin-right: 0.25rem;
        color: #94a3b8;
    }
    
    /* Leaderboard styles */
    .leaderboard-card {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-radius: 12px;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
        background-color: #f8fafc;
    }
    
    .leaderboard-card:hover {
        background-color: #f1f5f9;
        transform: translateX(3px);
    }
    
    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        margin-right: 0.75rem;
    }
    
    .rank-1 {
        background: linear-gradient(135deg, #ffd700 0%, #ffcc00 100%);
        color: #7e5c00;
        box-shadow: 0 2px 5px rgba(255, 198, 0, 0.3);
    }
    
    .rank-2 {
        background: linear-gradient(135deg, #e0e0e0 0%, #c0c0c0 100%);
        color: #505050;
        box-shadow: 0 2px 5px rgba(192, 192, 192, 0.3);
    }
    
    .rank-3 {
        background: linear-gradient(135deg, #cd7f32 0%, #b56d2c 100%);
        color: #6d4212;
        box-shadow: 0 2px 5px rgba(205, 127, 50, 0.3);
    }
    
    .rank-other {
        background: #e2e8f0;
        color: #64748b;
    }
    
    .points-badge {
        font-weight: 700;
        color: #3b82f6;
        font-size: 0.9rem;
        background: #eff6ff;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        margin-left: auto;
    }
    
    /* Tags styles */
    .tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .tag-item {
        background-color: #f1f5f9;
        color: #334155;
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .tag-item:hover {
        background-color: #3b82f6;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(59, 130, 246, 0.3);
    }
    
    /* View all buttons */
    .view-all-link {
        text-align: center;
        margin-top: 1rem;
    }
    
    .view-all-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(59, 130, 246, 0.2);
    }
    
    .view-all-button:hover {
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        transform: translateY(-2px);
    }
    
    .view-all-button i {
        margin-right: 0.5rem;
    }
    
    /* Empty states */
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: #94a3b8;
        font-size: 0.9rem;
    }
    
    .empty-state i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #cbd5e1;
    }
    
    /* Tùy chỉnh scrollbar cho tab-content */
    .tab-content {
        max-height: 350px;
        overflow-y: auto;
        scrollbar-width: none; /* Ẩn thanh cuộn mặc định trên Firefox */
        -ms-overflow-style: none; /* Ẩn thanh cuộn mặc định trên IE/Edge */
        scroll-behavior: smooth;
        mask-image: linear-gradient(to bottom, transparent, black 5%, black 95%, transparent);
        -webkit-mask-image: linear-gradient(to bottom, transparent, black 5%, black 95%, transparent);
        padding: 10px 5px 10px 0;
        margin: -10px -5px -10px 0;
    }
    
    .tab-content::-webkit-scrollbar {
        width: 5px;
        background: transparent;
    }
    
    .tab-content::-webkit-scrollbar-track {
        background: transparent;
        margin: 10px 0;
    }
    
    .tab-content::-webkit-scrollbar-thumb {
        background-color: rgba(203, 213, 225, 0);
        border-radius: 20px;
        transition: background-color 0.3s ease;
        border: 1px solid rgba(203, 213, 225, 0);
    }
    
    .tab-content:hover::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.5);
        border: 1px solid rgba(203, 213, 225, 0.1);
    }
    
    .tab-content:hover::-webkit-scrollbar-thumb:hover {
        background-color: rgba(148, 163, 184, 0.8);
    }
    
    /* Tạo hiệu ứng fade-out ở trên và dưới */
    .tab-content::before,
    .tab-content::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 25px;
        z-index: 10;
        pointer-events: none;
    }
    
    .tab-content::before {
        top: 0;
        background: linear-gradient(to bottom, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0) 100%);
    }
    
    .tab-content::after {
        bottom: 0;
        background: linear-gradient(to top, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0) 100%);
    }
    
    /* Ẩn thanh cuộn phần tử cha khi phần tử con đang cuộn */
    .aside-wrapper {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
        position: relative;
    }
    
    .aside-wrapper::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }
    
    /* Tạo hiệu ứng hover đặc biệt cho mỗi card item */
    .sidebar-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border-left: 3px solid transparent;
    }
    
    .sidebar-card:hover {
        background-color: rgba(241, 245, 249, 0.8);
        transform: translateX(5px);
        border-left: 3px solid #3b82f6;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    
    /* Thêm hiệu ứng loading khi chuyển tab */
    .tab-content.loading {
        position: relative;
        overflow: hidden;
    }
    
    .tab-content.loading::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 100;
    }
    
    .tab-content.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border: 3px solid rgba(59, 130, 246, 0.3);
        border-radius: 50%;
        border-top-color: #3b82f6;
        z-index: 101;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sidebar-section {
            padding: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .tabs-nav {
            gap: 0.5rem;
        }
        
        .tab-button {
            padding: 0.5rem 0.25rem;
            font-size: 0.85rem;
        }
        
        .sidebar-card {
            padding: 0.75rem;
        }
        
        .avatar-circle {
            width: 35px;
            height: 35px;
        }
        
        .card-content {
            margin-left: 0.5rem;
        }
        
        .card-title {
            font-size: 0.85rem;
        }
        
        .card-text {
            font-size: 0.8rem;
        }
        
        .tab-content {
            max-height: none;
            overflow-y: visible;
        }
    }
    
    /* Thêm styles cho container bên ngoài để phù hợp với index.blade.php */
    @media (min-width: 1024px) {
        .aside-wrapper {
            height: 100%;
            padding-right: 0.5rem;
        }
    }
</style>

<div class="aside-wrapper">
    <!-- Community Activity -->
    <div class="sidebar-section">
        <div class="flex justify-between items-center mb-4">
            <h2 class="sidebar-heading">Hoạt động cộng đồng</h2>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button id="tab-posts" class="tab-button active">Bài viết</button>
                <button id="tab-comments" class="tab-button">Bình luận</button>
                <button id="tab-groups" class="tab-button">Nhóm học tập</button>
            </div>
        </div>

        <!-- Posts Tab Content -->
        <div id="posts-content" class="tab-content">
            @forelse($latestPosts as $post)
            <div class="sidebar-card">
                <img src="{{ $post->author->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}" alt="User" class="avatar-circle">
                <div class="card-content">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="card-title">{{ $post->author->full_name ?? 'Người dùng' }}</h4>
                        <span class="card-subtitle">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                    <a href="{{ route('front.tblogs.show', $post->slug) }}" class="block hover:text-blue-600">
                        <p class="card-text">{{ Str::limit($post->title, 50) }}</p>
                    </a>
                    
                    <div class="card-meta">
                        <span class="meta-item">
                            <i class="far fa-thumbs-up"></i> {{ $post->likes_count ?? 0 }}
                        </span>
                        <span class="meta-item">
                            <i class="far fa-comment"></i> {{ $post->comment_count ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="far fa-newspaper"></i>
                <p>Chưa có bài viết nào.</p>
            </div>
            @endforelse
        </div>

        <!-- Comments Tab Content -->
        <div id="comments-content" class="tab-content hidden">
            @forelse($latestComments as $comment)
            <div class="sidebar-card">
                <img src="{{ $comment->author->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}" alt="User" class="avatar-circle">
                <div class="card-content">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="card-title">{{ $comment->author->name ?? 'Người dùng' }}</h4>
                        <span class="card-subtitle">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    @if($comment->tblog)
                    <a href="{{ route('front.tblogs.show', $comment->tblog->slug) }}#comment-{{ $comment->id }}" class="block text-xs text-blue-500 mb-1 hover:text-blue-700">
                        Trong bài viết: {{ Str::limit($comment->tblog->title, 40) }}
                    </a>
                    @elseif($comment->item_code == 'book' && $comment->book)
                    <a href="{{ route('front.book.show', $comment->book->slug) }}#comment-{{ $comment->id }}" class="block text-xs text-blue-500 mb-1 hover:text-blue-700">
                        Trong sách: {{ Str::limit($comment->book->title, 40) }}
                    </a>
                    @endif
                    <p class="card-text">{{ Str::limit(strip_tags($comment->content), 100) }}</p>
                    <div class="card-meta">
                        <span class="meta-item">
                            <i class="far fa-thumbs-up"></i> {{ $comment->likes_count ?? 0 }}
                        </span>
                        <span class="meta-item">
                            <i class="far fa-comment"></i> {{ $comment->replies_count ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="far fa-comments"></i>
                <p>Chưa có bình luận nào.</p>
            </div>
            @endforelse
        </div>

        <!-- Groups Tab Content -->
        <div id="groups-content" class="tab-content hidden">
            @forelse($activeGroups as $group)
            <a href="{{ route('group.show', $group->id) }}" class="block">
                <div class="sidebar-card">
                    <div class="avatar-circle flex items-center justify-center overflow-hidden">
                        @if($group->photo)
                            <img src="{{ $group->photo }}" alt="{{ $group->title }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-users text-blue-500"></i>
                        @endif
                    </div>
                    <div class="card-content">
                        <h4 class="card-title">{{ $group->title }}</h4>
                        <p class="card-text">{{ Str::limit($group->description, 80) }}</p>
                        <div class="card-meta">
                            <span class="meta-item">
                                <i class="fas fa-users"></i> {{ count(json_decode($group->members ?? '[]', true)) }} thành viên
                            </span>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <p>Chưa có nhóm nào.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Top Readers Leaderboard Section -->
    <div class="sidebar-section">
        <div class="flex justify-between items-center mb-4">
            <h2 class="sidebar-heading">Vinh danh bạn đọc</h2>
            
        </div>
        
        <div class="space-y-2">
            @forelse($topLeaderboardUsers as $index => $user)
                <div class="leaderboard-card">
                    <div class="rank-badge {{ $index+1 <= 3 ? 'rank-'.($index+1) : 'rank-other' }}">
                        {{ $index + 1 }}
                    </div>
                    <img src="{{ $user->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}" alt="{{ $user->full_name }}" class="avatar-circle">
                    <div class="ml-2 flex-1">
                        <h3 class="font-medium text-gray-800 text-sm">{{ Str::limit($user->full_name, 20) }}</h3>
                    </div>
                    <div class="points-badge">
                        {{ number_format($user->total_points) }}
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-trophy"></i>
                    <p>Chưa có dữ liệu xếp hạng.</p>
                </div>
            @endforelse
            
            <div class="view-all-link">
                <a href="{{ route('front.leaderboard') }}" class="view-all-button">
                    <i class="fas fa-trophy"></i> Xem bảng xếp hạng
                </a>
            </div>
        </div>
    </div>
    
     <!-- Popular Tags -->
     <div class="sidebar-section">
        <h2 class="sidebar-heading">Tags phổ biến</h2>

        <div class="tags-container">
            @forelse($tags as $tag)
            <a href="{{ route('front.book.search', ['tag' => $tag->id]) }}" class="tag-item">
                {{ $tag->title }}
            </a>
            @empty
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <p>Chưa có tags nào.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active state from all tabs
                tabs.forEach(t => {
                    t.classList.remove('active');
                });
                
                // Add active state to clicked tab
                tab.classList.add('active');
                
                // Hide all content sections
                contents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Add loading animation
                const contentId = tab.id.replace('tab-', '') + '-content';
                const targetContent = document.getElementById(contentId);
                
                // Simulating loading for smoother transitions
                targetContent.classList.add('loading');
                setTimeout(() => {
                    targetContent.classList.remove('hidden');
                    
                    // Remove loading after a short delay
                    setTimeout(() => {
                        targetContent.classList.remove('loading');
                    }, 300);
                }, 100);
            });
        });
        
        // Smooth scrolling for tab-content elements
        contents.forEach(content => {
            content.addEventListener('wheel', function(e) {
                const scrollSpeed = 30; // Điều chỉnh tốc độ cuộn
                
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    this.scrollTop += (e.deltaY > 0) ? scrollSpeed : -scrollSpeed;
                }
            }, { passive: false });
        });
    });
</script>
