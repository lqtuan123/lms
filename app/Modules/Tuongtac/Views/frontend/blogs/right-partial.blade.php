<?php
use App\Modules\Tuongtac\Models\TPage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

// Lấy user ID nếu đã đăng nhập
$userId = Auth::check() ? Auth::id() : null;

// Get new posts
$newposts = \App\Modules\Tuongtac\Models\TBlog::with('group')
    ->where('status', 1)
    ->where(function ($query) use ($userId) {
        // Bài viết không thuộc nhóm nào
        $query->where(function ($q) {
            $q->whereNull('group_id')->orWhere('group_id', 0);
        });

        // HOẶC bài viết thuộc nhóm công khai
        $query->orWhereHas('group', function ($q) {
            $q->where('is_private', 0)->where('status', 'active');
        });

        // HOẶC bài viết thuộc nhóm riêng tư nhưng người dùng là thành viên (nếu đã đăng nhập)
        if ($userId) {
            $query->orWhereHas('group', function ($q) use ($userId) {
                $q->where('is_private', 1)
                    ->where('status', 'active')
                    ->where(function ($subQ) use ($userId) {
                        // Người dùng là thành viên
                        $subQ->whereRaw("JSON_CONTAINS(members, '\"$userId\"')");
                        // Hoặc là người tạo nhóm
                        $subQ->orWhere('author_id', $userId);
                        // Hoặc là phó nhóm
                        $subQ->orWhereRaw("JSON_CONTAINS(moderators, '\"$userId\"')");
                    });
            });
        }
    })
    ->inRandomOrder()
    ->limit(10)
    ->get();

// Get popular posts
$popularPosts = \App\Modules\Tuongtac\Models\TBlog::with('group')
    ->where('status', 1)
    ->where(function ($query) use ($userId) {
        // Bài viết không thuộc nhóm nào
        $query->where(function ($q) {
            $q->whereNull('group_id')->orWhere('group_id', 0);
        });

        // HOẶC bài viết thuộc nhóm công khai
        $query->orWhereHas('group', function ($q) {
            $q->where('is_private', 0)->where('status', 'active');
        });

        // HOẶC bài viết thuộc nhóm riêng tư nhưng người dùng là thành viên (nếu đã đăng nhập)
        if ($userId) {
            $query->orWhereHas('group', function ($q) use ($userId) {
                $q->where('is_private', 1)
                    ->where('status', 'active')
                    ->where(function ($subQ) use ($userId) {
                        // Người dùng là thành viên
                        $subQ->whereRaw("JSON_CONTAINS(members, '\"$userId\"')");
                        // Hoặc là người tạo nhóm
                        $subQ->orWhere('author_id', $userId);
                        // Hoặc là phó nhóm
                        $subQ->orWhereRaw("JSON_CONTAINS(moderators, '\"$userId\"')");
                    });
            });
        }
    })
    ->orderBy('hit', 'desc')
    ->limit(10)
    ->get();

// Get active groups with most members
use App\Modules\Group\Models\Group;
$groups = Group::where('status', 'active')
    ->orderByRaw('JSON_LENGTH(members) DESC') // Sắp xếp theo số lượng thành viên (giảm dần)
    ->limit(10) // Giới hạn 10 nhóm
    ->get();
?>

<style>
    /* CSS cho right sidebar */
    .right-sidebar {
        width: 320px;
        position: sticky;
        top: 0;
        height: 100vh;
        /* Thanh cuộn đã được xử lý trong file body.blade.php */
        padding: 1rem;
        border-left: 1px solid #e5e7eb;
    }

    /* Section styles - thay thế cho right-card để tránh tạo thêm thanh cuộn */
    .right-section {
        background-color: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Heading styles */
    .right-heading {
        font-weight: 600;
        font-size: 1rem;
        color: #374151;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    /* Menu styles */
    .right-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .right-menu-item {
        display: flex;
        align-items: center;
        padding: 0.625rem 0.5rem;
        border-radius: 0.375rem;
        color: #4b5563;
        font-size: 0.9375rem;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease;
    }

    .right-menu-item:hover {
        background-color: #f3f4f6;
        color: #2563eb;
    }

    .right-menu-item.active {
        background-color: #e5e7eb;
        color: #2563eb;
        font-weight: 500;
    }

    /* Group styles */
    .group-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .group-avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .group-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .group-info {
        margin-left: 0.75rem;
    }

    .group-title {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
    }

    .group-members {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Post styles */
    .post-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .post-thumbnail {
        width: 4rem;
        height: 4rem;
        border-radius: 0.375rem;
        overflow: hidden;
        flex-shrink: 0;
    }

    .post-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .post-info {
        margin-left: 0.75rem;
        flex: 1;
        min-width: 0;
        /* Đảm bảo có thể co lại khi cần */
    }

    .post-title {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        line-height: 1.4;
    }

    .post-stats {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    /* User styles */
    .user-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .user-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-name {
        margin-left: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
    }

    /* Link styles */
    .view-all-link {
        display: block;
        text-align: center;
        color: #3b82f6;
        font-size: 0.875rem;
        margin-top: 1rem;
        transition: color 0.2s;
    }

    .view-all-link:hover {
        color: #2563eb;
    }

    /* Mobile styles */
    .right-sidebar-mobile {
        display: none;
    }

    /* Mobile card for horizontal scroll */
    .mobile-scroll-container {
        display: flex;
        overflow-x: auto;
        scrollbar-width: none;
        /* Firefox */
        padding-bottom: 0.5rem;
    }

    .mobile-scroll-container::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari, Opera */
    }

    .mobile-card {
        flex: 0 0 auto;
        width: 16rem;
        margin-right: 1rem;
    }

    @media (max-width: 992px) {
        .right-sidebar {
            display: none;
        }

        .right-sidebar-mobile {
            display: block;
            margin-top: 1.5rem;
            overflow-x: hidden;
            /* Tránh thanh cuộn ngang toàn bộ */
        }
    }
</style>

<!-- Main Navigation -->
<div class="right-section">
    <h3 class="right-heading">
        <i class="fas fa-compass mr-2 text-blue-500"></i>
        Điều hướng
    </h3>
    <ul class="right-menu">
        <li>
            <a href="{{ route('front.tblogs.index') }}" class="right-menu-item {{ request()->routeIs('front.tblogs.index') && !request()->query('filter') ? 'active' : '' }}">
                <i class="fas fa-home mr-2"></i> Trang chủ
            </a>
        </li>
        <li>
            <a href="{{ route('front.tblogs.trendblog') }}" class="right-menu-item {{ request()->routeIs('front.tblogs.trendblog') ? 'active' : '' }}">
                <i class="fas fa-fire mr-2"></i> Xu hướng
            </a>
        </li>
        <li>
            <a href="{{ route('front.tblogs.favblog') }}" class="right-menu-item {{ request()->routeIs('front.tblogs.favblog') ? 'active' : '' }}">
                <i class="fas fa-heart mr-2"></i> Yêu thích
            </a>
        </li>
        @auth
        <li>
            <a href="{{ route('front.tblogs.myblog') }}" class="right-menu-item {{ request()->routeIs('front.tblogs.myblog') ? 'active' : '' }}">
                <i class="fas fa-pen-alt mr-2"></i> Bài viết của tôi
            </a>
        </li>
        @endauth
    </ul>
</div>

<!-- Groups -->
<div class="right-section">
    <h3 class="right-heading">
        <i class="fas fa-users mr-2 text-green-500"></i>
        <a href="{{ route('group.index') }}" class="hover:text-blue-600">Nhóm thành viên</a>
    </h3>

    <div class="space-y-3">
        @foreach ($groups as $group)
            <div class="group-item">
                <div class="group-avatar">
                    @if ($group->photo)
                        <img src="{{ $group->photo }}" alt="{{ $group->title }}">
                    @else
                        <div class="w-full h-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-users text-blue-500"></i>
                        </div>
                    @endif
                </div>
                <div class="group-info">
                    <a href="{{ route('group.show', $group->id) }}" class="group-title">
                        {{ Str::limit($group->title, 20) }}
                    </a>
                    <div class="group-members">
                        <i class="fas fa-users text-gray-400 mr-1"></i> {{ $group->getMemberCount() }} thành viên
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('group.index') }}" class="view-all-link">Xem tất cả</a>
</div>

<!-- Recent Posts -->
<div class="right-section">
    <h3 class="right-heading">
        <i class="fas fa-newspaper mr-2 text-red-500"></i>
        Có thể bạn quan tâm
    </h3>

    <div class="space-y-4">
        @foreach ($newposts as $post)
            <?php
            $images = json_decode($post->photo, true);
            if (!$images) {
                $thumbnail_url = 'https://itcctv.vn/images/profile-8.jpg';
            } else {
                $thumbnail_url = $images[0];
            }
            ?>
            <div class="post-item">
                <div class="post-thumbnail">
                    <img src="{{ $thumbnail_url }}" alt="{{ $post->title }}">
                </div>
                <div class="post-info">
                    <a href="{{ route('front.tblogs.show', $post->slug) }}" class="post-title">
                        {{ Str::limit($post->title, 45) }}
                    </a>
                    <div class="post-stats">
                        <i class="fas fa-eye text-gray-400 mr-1"></i> {{ $post->hit ?? 0 }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('front.tblogs.index') }}" class="view-all-link">Xem thêm</a>
</div>

<!-- Ad Banner -->
<div class="right-section">
    <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-5437344106154965" data-ad-slot="1550593306"
        data-ad-format="auto" data-full-width-responsive="true"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>

<!-- Popular Posts -->
<div class="right-section">
    <h3 class="right-heading">
        <i class="fas fa-fire mr-2 text-orange-500"></i>
        Bài viết phổ biến
    </h3>

    <div class="space-y-4">
        @foreach ($popularPosts as $post)
            <?php
            $images = json_decode($post->photo, true);
            if (!$images) {
                $thumbnail_url = 'https://itcctv.vn/images/profile-8.jpg';
            } else {
                $thumbnail_url = $images[0];
            }
            ?>
            <div class="post-item">
                <div class="post-thumbnail">
                    <img src="{{ $thumbnail_url }}" alt="{{ $post->title }}">
                </div>
                <div class="post-info">
                    <a href="{{ route('front.tblogs.show', $post->slug) }}" class="post-title">
                        {{ Str::limit($post->title, 45) }}
                    </a>
                    <div class="post-stats">
                        <i class="fas fa-fire text-orange-400 mr-1"></i> {{ $post->hit ?? 0 }} lượt xem
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('front.tblogs.trendblog') }}" class="view-all-link">Xem thêm</a>
</div>

<!-- Ad Banner -->
<div class="right-section">
    <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-5437344106154965" data-ad-slot="7114573880"
        data-ad-format="auto" data-full-width-responsive="true"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>

<!-- Ad Banner -->
<div class="right-section">
    <ins class="adsbygoogle" style="display:block" data-ad-format="autorelaxed" data-ad-client="ca-pub-5437344106154965"
        data-ad-slot="2431624238"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>

<!-- Right Sidebar for Mobile (Slider) -->
<aside class="right-sidebar-mobile">
    <div class="right-section">
        <div class="mobile-scroll-container">
            <!-- Recent Posts Card -->
            <div class="mobile-card">
                <h3 class="right-heading">
                    <i class="fas fa-newspaper mr-2 text-red-500"></i>
                    Bài viết mới
                </h3>

                <div class="space-y-4">
                    @foreach ($newposts->take(3) as $post)
                        <?php
                        $images = json_decode($post->photo, true);
                        if (!$images) {
                            $thumbnail_url = 'https://itcctv.vn/images/profile-8.jpg';
                        } else {
                            $thumbnail_url = $images[0];
                        }
                        ?>
                        <div class="post-item">
                            <div class="post-thumbnail">
                                <img src="{{ $thumbnail_url }}" alt="{{ $post->title }}">
                            </div>
                            <div class="post-info">
                                <a href="{{ route('front.tblogs.show', $post->slug) }}" class="post-title">
                                    {{ Str::limit($post->title, 30) }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Popular Posts Card -->
            <div class="mobile-card">
                <h3 class="right-heading">
                    <i class="fas fa-fire mr-2 text-orange-500"></i>
                    Bài nổi bật
                </h3>

                <div class="space-y-4">
                    @foreach ($popularPosts->take(3) as $post)
                        <?php
                        $images = json_decode($post->photo, true);
                        if (!$images) {
                            $thumbnail_url = 'https://itcctv.vn/images/profile-8.jpg';
                        } else {
                            $thumbnail_url = $images[0];
                        }
                        ?>
                        <div class="post-item">
                            <div class="post-thumbnail">
                                <img src="{{ $thumbnail_url }}" alt="{{ $post->title }}">
                            </div>
                            <div class="post-info">
                                <a href="{{ route('front.tblogs.show', $post->slug) }}" class="post-title">
                                    {{ Str::limit($post->title, 30) }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Groups Card -->
            <div class="mobile-card">
                <h3 class="right-heading">
                    <i class="fas fa-users mr-2 text-green-500"></i>
                    Nhóm
                </h3>

                <div class="space-y-3">
                    @foreach ($groups->take(3) as $group)
                        <div class="group-item">
                            <div class="group-avatar">
                                @if ($group->photo)
                                    <img src="{{ $group->photo }}" alt="{{ $group->title }}">
                                @else
                                    <div class="w-full h-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-users text-blue-500"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="group-info">
                                <a href="{{ route('group.show', $group->id) }}" class="group-title">
                                    {{ Str::limit($group->title, 15) }}
                                </a>
                                <div class="group-members">
                                    <i class="fas fa-users text-gray-400 mr-1"></i> {{ $group->getMemberCount() }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</aside>

<script>
    // Make sure we have a single instance of each function
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure bookmark buttons work
        document.querySelectorAll('.btn-bookmark').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const postId = this.getAttribute('data-post-id');
                if (postId) {
                    bookmarkPost(postId);
                }
            });
        });

        // Ensure reaction buttons work
        document.querySelectorAll('.btn-reaction').forEach(button => {
            button.addEventListener('click', function() {
                const reactionId = this.getAttribute('data-reaction-id');
                const postId = this.getAttribute('data-id');
                const itemCode = this.getAttribute('item_code');
                reactToPost(reactionId, postId, itemCode);
            });
        });
    });
</script>
