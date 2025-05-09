@extends('frontend.profile.body')
@section('topcss')
    <link rel="stylesheet" href="{{ asset('frontend/css/profile.css') }}">
    <style>
        /* CSS cho tab content */
        .tab-contents .tab-content {
            display: none;
        }

        .tab-contents .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Hiệu ứng và transitions */
        .text-highlight {
            transition: all 0.5s ease;
            background-color: rgba(59, 130, 246, 0.1);
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
            border-radius: 4px;
            padding: 0 4px;
        }

        .post-card,
        .bg-white.border {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .post-card:hover,
        .bg-white.border:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Form components */
        .profile-form-transition {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out;
        }

        .profile-form-transition.open {
            max-height: 1000px;
        }

        #info-view-mode,
        #info-edit-mode {
            transition: opacity 0.2s ease-in-out;
        }

        /* Buttons */
        button[type="submit"],
        .bookmark-btn,
        .post-action-btn {
            transition: all 0.2s ease;
        }

        button[type="submit"]:hover,
        .bookmark-btn:hover,
        .post-action-btn:hover {
            transform: translateY(-2px);
        }

        button[type="submit"]:active,
        .bookmark-btn:active,
        .post-action-btn:active {
            transform: translateY(0);
        }
        
        /* Actions & Inputs */
        .post-action-btn {
            transition: all 0.2s ease;
        }

        .post-action-btn:hover,
        .post-action-btn.active {
            color: #3b82f6 !important;
        }
        
        .comment-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }
    </style>
@endsection
@section('inner-content')
    <!-- Main Content -->

    <!-- Main Content Area -->
    <div id="main-content" class="main-content lg:w-3/5 lg:px-4">
        <!-- Profile Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="flex overflow-x-auto">
                <button
                    class="tab-button flex-shrink-0 px-4 py-3 border-b-2 border-blue-500 text-blue-600 font-medium active"
                    data-tab="posts">
                    Bài viết đã đăng
                </button>
                <button
                    class="tab-button flex-shrink-0 px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-blue-600"
                    data-tab="personal-info">
                    Thông tin cá nhân
                </button>
                <button
                    class="tab-button flex-shrink-0 px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-blue-600"
                    data-tab="books">
                    Sách đã đăng
                </button>
                <button
                    class="tab-button flex-shrink-0 px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-blue-600"
                    data-tab="likes">
                    Đã thích
                </button>
            </div>
        </div>

        <!-- Tab Contents -->
        <div class="tab-contents">
            <!-- Posts Tab -->
            <div id="posts" class="tab-content active">
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Bài viết đã đăng ({{ $postCount ?? 0 }})</h2>
                        @if (isset($isOwner) && $isOwner)
                            <a href="{{ route('front.tblogs.create') }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                                <i class="fas fa-plus mr-2"></i> Tạo bài viết mới
                            </a>
                        @endif
                    </div>

                    <!-- Post Feed -->
                    <div class="space-y-6">
                        @if (
                            (isset($isOwner) && $isOwner) ||
                                !isset($privacySettings) ||
                                (isset($privacySettings) && !$privacySettings->hide_posts))
                            @if (isset($userPosts) && count($userPosts) > 0)
                                <div class="grid grid-cols-1 gap-6">
                                    @foreach ($userPosts as $post)
                                        @if ($post->status == 1 || (isset($isOwner) && $isOwner))
                                            <div
                                                class="post-card p-4 relative {{ $post->status == 0 ? 'bg-gray-50' : '' }}">
                                                <!-- Action buttons -->
                                                @if (isset($isOwner) && $isOwner)
                                                    <div class="absolute top-3 right-3 z-10 flex space-x-2">
                                                        <form action="{{ route('front.tblogs.destroy', $post->id) }}"
                                                            method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                                                                title="Xóa bài viết"
                                                                onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');">
                                                                <i class="fas fa-trash-alt text-gray-600 text-sm"></i>
                                                            </button>
                                                        </form>

                                                        <a href="{{ route('front.tblogs.edit', $post->id) }}"
                                                            class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                                                            title="Chỉnh sửa">
                                                            <i class="fas fa-pencil-alt text-gray-600 text-sm"></i>
                                                        </a>

                                                        @if ($post->status == 1)
                                                            <a href="{{ route('front.tblogs.status', $post->id) }}"
                                                                class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                                                                title="Ẩn bài viết">
                                                                <i class="fas fa-eye-slash text-gray-600 text-sm"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('front.tblogs.status', $post->id) }}"
                                                                class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                                                                title="Hiện bài viết">
                                                                <i class="fas fa-eye text-gray-600 text-sm"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="post-card-header">
                                                    <a
                                                        href="{{ route('front.user.profile', ['id' => $post->author ? $post->author->id : 0]) }}">
                                                        <img src="{{ $post->author_photo ? (strpos($post->author_photo, 'http') === 0 ? $post->author_photo : asset($post->author_photo)) : asset('images/default-avatar.png') }}"
                                                            alt="Avatar" class="avatar-img">
                                                    </a>
                                                    <div>
                                                        <h3 class="font-medium text-gray-800">
                                                            <a href="{{ route('front.user.profile', ['id' => $post->author ? $post->author->id : 0]) }}"
                                                                class="hover:text-blue-600">
                                                                {{ $post->author_name ?? 'Người dùng không xác định' }}
                                                            </a>
                                                        </h3>
                                                        <p class="text-xs text-gray-500">
                                                            {{ \Carbon\Carbon::parse($post->created_at)->locale('vi')->diffForHumans() }}
                                                            ·
                                                            <i
                                                                class="fas fa-{{ $post->status == 1 ? 'globe-americas' : 'lock' }} text-xs"></i>
                                                        </p>
                                                        @if (isset($post->group_name) && isset($post->group_url))
                                                            <p class="text-xs text-blue-500 mt-1">
                                                                <i class="fas fa-users mr-1"></i>
                                                                <a href="{{ $post->group_url }}" class="hover:underline">
                                                                    {{ $post->group_name }}
                                                                </a>
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <h2 class="post-title">
                                                        <a href="{{ route('front.tblogs.show', $post->slug) }}">
                                                            {{ $post->title }}
                                                        </a>
                                                    </h2>

                                                    <p class="post-summary">
                                                        {{ Str::limit(strip_tags($post->content), 150) }}
                                                    </p>

                                                    @if (count($post->tags) > 0)
                                                        <div class="flex flex-wrap mb-3">
                                                            @foreach ($post->tags as $tag)
                                                                <a href="{{ route('front.tblogs.tag', $tag->slug) }}"
                                                                    class="post-tag">
                                                                    #{{ $tag->title }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @php
                                                        $images = json_decode($post->photo, true);
                                                        $thumbnail_url = null;
                                                        if ($images && is_array($images) && count($images) > 0) {
                                                            $thumbnail_url = $images[0];
                                                        }
                                                    @endphp

                                                    @if ($thumbnail_url)
                                                        <img src="{{ $thumbnail_url }}" alt="{{ $post->title }}"
                                                            class="post-image">
                                                    @endif
                                                </div>

                                                <div class="post-stats">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center">
                                                            <i class="fas fa-thumbs-up text-blue-500 mr-1"></i>
                                                            <span class="text-xs"
                                                                id="like-count-{{ $post->id }}">{{ $post->total_likes ?? $post->likes_count ?? 0 }}</span>
                                                        </div>
                                                        <div class="flex items-center ml-4">
                                                            <i class="fas fa-comment text-gray-400 mr-1"></i>
                                                            <span class="text-xs">{{ $post->comment_count ?? 0 }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-xs">{{ $post->share_count ?? 0 }} lượt chia sẻ
                                                    </div>
                                                </div>

                                                <div class="post-actions">
                                                    <button id="like-btn-{{ $post->id }}"
                                                        class="post-action-btn {{ isset($post->user_has_liked) && $post->user_has_liked ? 'liked text-blue-500' : '' }}"
                                                        data-item-id="{{ $post->id }}" data-item-code="tblog">
                                                        <i
                                                            class="{{ isset($post->user_has_liked) && $post->user_has_liked ? 'fas' : 'far' }} fa-thumbs-up"></i>
                                                        Thích
                                                    </button>
                                                    <button onclick="toggleCommentBox({{ $post->id }}, 'tblog')"
                                                        class="post-action-btn">
                                                        <i class="far fa-comment"></i> Bình luận
                                                    </button>
                                                    <button
                                                        onclick="sharePost({{ $post->id }}, 'tblog', '{{ $post->slug }}')"
                                                        class="post-action-btn">
                                                        <i class="fas fa-share"></i> Chia sẻ
                                                    </button>
                                                    <button id="bookmark-btn-{{ $post->id }}"
                                                        onclick="toggleBookmark({{ $post->id }}, 'tblog')"
                                                        class="post-action-btn {{ isset($post->is_bookmarked) && $post->is_bookmarked ? 'text-red-500' : '' }}">
                                                        <i
                                                            class="{{ isset($post->is_bookmarked) && $post->is_bookmarked ? 'fas' : 'far' }} fa-heart"></i>
                                                        Yêu thích
                                                    </button>
                                                </div>

                                                <div class="comment-input-container">
                                                    <img src="{{ auth()->user()->photo ?? 'https://randomuser.me/api/portraits/women/44.jpg' }}"
                                                        alt="User" class="w-8 h-8 rounded-full object-cover mr-2">
                                                    <div class="relative flex-1">
                                                        <input type="text" id="comment-input-{{ $post->id }}"
                                                            style="width: 100%;" placeholder="Viết bình luận..."
                                                            class="comment-input">
                                                        <div
                                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-1">
                                                            <button class="text-gray-400 hover:text-gray-600 emoji-trigger"
                                                                onclick="addEmoji({{ $post->id }}, event, 'tblog')"
                                                                data-item-id="{{ $post->id }}">
                                                                <i class="far fa-smile"></i>
                                                            </button>
                                                            <button class="text-gray-400 hover:text-gray-600"
                                                                onclick="submitComment({{ $post->id }}, 'tblog')">
                                                                <i class="fas fa-paper-plane"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Comment Box -->
                                                <div id="comment-box-{{ $post->id }}"
                                                    class="comment-box bg-white rounded-lg shadow-sm p-4 mt-3"
                                                    style="display: none;">
                                                    <div id="comments-container-{{ $post->id }}" class="space-y-3">
                                                        <!-- Comments will be loaded here dynamically -->
                                                        <div class="text-center text-gray-500 text-sm py-2">
                                                            <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải bình
                                                            luận...
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                @if (isset($isOwner) && $isOwner && isset($userPosts) && count($userPosts) >= 3)
                                    <div class="mt-6 text-center">
                                        <a href="{{ route('front.tblogs.myblog') }}"
                                            class="inline-block px-5 py-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100">
                                            Xem tất cả bài viết <i class="fas fa-chevron-right ml-1"></i>
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-8">
                                    <p class="text-gray-500">
                                        {{ isset($isOwner) && $isOwner ? 'Bạn chưa đăng bài viết nào' : 'Người dùng chưa đăng bài viết nào' }}
                                    </p>
                                    @if (isset($isOwner) && $isOwner)
                                        <a href="{{ route('front.tblogs.create') }}"
                                            class="mt-3 inline-block px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                            <i class="fas fa-pencil-alt mr-2"></i> Viết bài ngay
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500">Người dùng đã giới hạn quyền xem bài viết</p>
                                <div class="mt-3 text-sm text-gray-400">
                                    <i class="fas fa-lock mr-1"></i> Nội dung này đã được bảo vệ bởi chủ sở hữu
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Personal Info Tab -->
            <div id="personal-info" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Thông tin cá nhân</h2>
                        @if (isset($isOwner) && $isOwner)
                            <button id="edit-info-btn" type="button"
                                class="text-blue-500 hover:text-blue-700 flex items-center">
                                <i class="fas fa-pencil-alt mr-1"></i> Chỉnh sửa
                            </button>
                            <button id="cancel-edit-info-btn" type="button"
                                class="text-gray-500 hover:text-gray-700 flex items-center hidden">
                                <i class="fas fa-times mr-1"></i> Hủy
                            </button>
                        @endif
                    </div>

                    <!-- View Mode -->
                    @if (
                        (isset($isOwner) && $isOwner) ||
                            !isset($privacySettings) ||
                            (isset($privacySettings) && !$privacySettings->hide_personal_info))
                        <div id="info-view-mode">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Họ và tên</h3>
                                    <p class="text-gray-800">{{ $profile->full_name ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Email</h3>
                                    <p class="text-gray-800">{{ $profile->email ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Địa chỉ</h3>
                                    <p class="text-gray-800">{{ $profile->address ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Số điện thoại</h3>
                                    <p class="text-gray-800">{{ $profile->phone ?? 'Chưa cập nhật' }}</p>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Tiểu sử</h3>
                                <p class="text-gray-800 whitespace-pre-line">
                                    {{ $profile->description ?? 'Chưa cập nhật tiểu sử.' }}</p>
                            </div>

                            <!-- Thêm nút đổi mật khẩu nếu là chủ tài khoản -->
                            @if (isset($isOwner) && $isOwner)
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <button id="change-password-btn" type="button"
                                        class="px-4 py-2 bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-key mr-2"></i> Đổi mật khẩu
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Edit Mode (Only for Owner) -->
                        @if (isset($isOwner) && $isOwner)
                            <div id="info-edit-mode" class="hidden">
                                <form id="profile-info-form" action="{{ route('front.profile.updatefield') }}"
                                    method="POST">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="full_name" class="block text-sm font-medium text-gray-500 mb-1">Họ
                                                và tên</label>
                                            <input type="text" id="full_name" name="full_name"
                                                value="{{ $profile->full_name }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="email"
                                                class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                                            <input type="email" id="email" value="{{ $profile->email }}"
                                                class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md"
                                                readonly>
                                        </div>
                                        <div>
                                            <label for="address" class="block text-sm font-medium text-gray-500 mb-1">Địa
                                                chỉ</label>
                                            <input type="text" id="address" name="address"
                                                value="{{ $profile->address ?? '' }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-500 mb-1">Số
                                                điện
                                                thoại</label>
                                            <input type="tel" id="phone" name="phone"
                                                value="{{ $profile->phone ?? '' }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-gray-200">
                                        <label for="description" class="block text-sm font-medium text-gray-500 mb-2">Tiểu
                                            sử</label>
                                        <textarea id="description" name="description" rows="4"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $profile->description ?? '' }}</textarea>
                                    </div>

                                    <!-- Giữ lại các trường ảnh từ form trước -->
                                    <input type="hidden" name="photo_old" value="{{ $profile->photo ?? '' }}">
                                    <input type="hidden" name="banner_old" value="{{ $profile->banner ?? '' }}">

                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="button" id="cancel-info-btn"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                            Hủy
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                            Lưu thay đổi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">Người dùng đã giới hạn quyền xem thông tin cá nhân</p>
                            <div class="mt-3 text-sm text-gray-400">
                                <i class="fas fa-lock mr-1"></i> Nội dung này đã được bảo vệ bởi chủ sở hữu
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Books Tab -->
            <div id="books" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Sách đã đăng ({{ $books->count() ?? 0 }})</h2>
                        @if (isset($isOwner) && $isOwner)
                            <a href="{{ route('front.book.create') }}"
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                <i class="fas fa-plus mr-2"></i> Thêm sách mới
                            </a>
                        @endif
                    </div>

                    @if (
                        (isset($isOwner) && $isOwner) ||
                            !isset($privacySettings) ||
                            (isset($privacySettings) && !$privacySettings->hide_books))
                        @if (isset($books) && $books->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach ($books as $book)
                                    <div
                                        class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                        <a href="{{ route('front.book.show', $book->id) }}"
                                            class="block h-40 overflow-hidden rounded-t-lg">
                                            <img src="{{ $book->photo ? (strpos($book->photo, 'http') === 0 ? $book->photo : asset($book->photo)) : asset('images/default-book.jpg') }}"
                                                alt="{{ $book->title }}"
                                                class="w-full h-full object-cover transition-transform hover:scale-105">
                                        </a>
                                        <div class="p-3">
                                            <h3 class="font-medium text-gray-800 line-clamp-2 mb-1">
                                                <a href="{{ route('front.book.show', $book->id) }}"
                                                    class="hover:text-blue-600">
                                                    {{ $book->title }}
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-500 mb-2">
                                                {{ Str::limit($book->short_description, 60) }}</p>
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center">
                                                    <span class="text-yellow-400 text-sm mr-1">
                                                        <i class="fas fa-star"></i>
                                                    </span>
                                                    <span class="text-sm text-gray-600">
                                                        {{ number_format($book->rating ?? 0, 1) }}
                                                    </span>
                                                </div>
                                                @if (isset($isOwner) && $isOwner)
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route('user.books.edit', $book->id) }}"
                                                            class="text-blue-500 hover:text-blue-700" title="Chỉnh sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('user.books.destroy', $book->id) }}"
                                                            method="POST" class="inline delete-book-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700"
                                                                title="Xóa sách">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6">
                                {{ $books->links() }}
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500">
                                    {{ isset($isOwner) && $isOwner ? 'Bạn chưa đăng sách nào' : 'Người dùng chưa đăng sách nào' }}
                                </p>
                                @if (isset($isOwner) && $isOwner)
                                    <a href="{{ route('front.book.create') }}"
                                        class="mt-3 inline-block px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                        <i class="fas fa-book mr-2"></i> Thêm sách mới
                                    </a>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">Người dùng đã giới hạn quyền xem sách đã đăng</p>
                            <div class="mt-3 text-sm text-gray-400">
                                <i class="fas fa-lock mr-1"></i> Nội dung này đã được bảo vệ bởi chủ sở hữu
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Likes Tab -->
            <div id="likes" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">
                        {{ isset($isOwner) && $isOwner ? 'Đã thích' : 'Yêu thích của người dùng' }}</h2>

                    @if (
                        (isset($isOwner) && $isOwner) ||
                            !isset($privacySettings) ||
                            (isset($privacySettings) && !$privacySettings->hide_favorites))
                        @if (isset($favoriteBooks) && count($favoriteBooks) > 0)
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold mb-4 text-gray-700">Sách yêu thích</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    @foreach ($favoriteBooks as $book)
                                        <div
                                            class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                            <a href="{{ route('front.book.show', $book->id) }}"
                                                class="block h-40 overflow-hidden rounded-t-lg">
                                                <img src="{{ $book->photo ? (strpos($book->photo, 'http') === 0 ? $book->photo : asset($book->photo)) : asset('images/default-book.jpg') }}"
                                                    alt="{{ $book->title }}"
                                                    class="w-full h-full object-cover transition-transform hover:scale-105">
                                            </a>
                                            <div class="p-3">
                                                <h3 class="font-medium text-gray-800 line-clamp-2 mb-1">
                                                    <a href="{{ route('front.book.show', $book->id) }}"
                                                        class="hover:text-blue-600">
                                                        {{ $book->title }}
                                                    </a>
                                                </h3>
                                                <p class="text-sm text-gray-500 mb-2">
                                                    {{ Str::limit($book->short_description, 60) }}</p>
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <span class="text-yellow-400 text-sm mr-1">
                                                            <i class="fas fa-star"></i>
                                                        </span>
                                                        <span class="text-sm text-gray-600">
                                                            {{ number_format($book->rating ?? 0, 1) }}
                                                        </span>
                                                    </div>
                                                    @if (isset($isOwner) && $isOwner)
                                                        <button type="button"
                                                            class="text-red-500 hover:text-red-700 bookmark-btn"
                                                            data-item-id="{{ $book->id }}" data-item-code="book"
                                                            title="Bỏ yêu thích">
                                                            <i class="fas fa-heart"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if (isset($isOwner) && $isOwner)
                                <div class="text-center mt-4">
                                    <a href="{{ route('front.book.index') }}"
                                        class="inline-block px-5 py-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100">
                                        Xem nhiều sách hơn <i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500">
                                    {{ isset($isOwner) && $isOwner ? 'Bạn chưa thích sách nào' : 'Người dùng chưa thích sách nào' }}
                                </p>
                                @if (isset($isOwner) && $isOwner)
                                    <a href="{{ route('front.book.index') }}"
                                        class="mt-3 inline-block px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                        <i class="fas fa-book-open mr-2"></i> Khám phá sách
                                    </a>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">Người dùng đã giới hạn quyền xem nội dung yêu thích</p>
                            <div class="mt-3 text-sm text-gray-400">
                                <i class="fas fa-lock mr-1"></i> Nội dung này đã được bảo vệ bởi chủ sở hữu
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Chỉnh sửa hồ sơ</h3>
                        <button type="button" id="close-edit-profile-modal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="edit-profile-form" action="{{ route('front.profile.update') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="edit_full_name" class="block text-sm font-medium text-gray-500 mb-1">Họ và
                                    tên</label>
                                <input type="text" id="edit_full_name" name="full_name"
                                    value="{{ $profile->full_name }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="edit_email" class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                                <input type="email" id="edit_email" value="{{ $profile->email }}"
                                    class="w-full px-4 py-2 border border-gray-300 bg-gray-100 rounded-md" readonly>
                            </div>
                            <div>
                                <label for="edit_address" class="block text-sm font-medium text-gray-500 mb-1">Địa
                                    chỉ</label>
                                <input type="text" id="edit_address" name="address"
                                    value="{{ $profile->address ?? '' }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="edit_phone" class="block text-sm font-medium text-gray-500 mb-1">Số điện
                                    thoại</label>
                                <input type="tel" id="edit_phone" name="phone" value="{{ $profile->phone }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label for="edit_description" class="block text-sm font-medium text-gray-500 mb-2">Tiểu sử
                                (Bio)</label>
                            <textarea id="edit_description" name="description" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $profile->description ?? '' }}</textarea>
                        </div>

                        <!-- Giữ lại các trường ảnh từ form trước -->
                        <input type="hidden" name="photo_old" value="{{ $profile->photo ?? '' }}">
                        <input type="hidden" name="banner_old" value="{{ $profile->banner ?? '' }}">

                        <div class="mt-6 flex justify-end">
                            <button type="button" id="cancel-edit-form-btn"
                                class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Hủy
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Update Modal -->
    <div id="avatar-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Cập nhật ảnh đại diện</h3>
                        <button type="button" id="close-avatar-modal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="mb-6">
                        <div class="flex flex-col items-center">
                            <div class="relative w-32 h-32 bg-gray-100 rounded-full overflow-hidden mb-4">
                                <img id="avatar-preview-modal"
                                    src="{{ $profile->photo ? (strpos($profile->photo, 'http') === 0 ? $profile->photo : asset($profile->photo)) : asset('backend/images/profile-6.jpg') }}"
                                    alt="Avatar" class="w-full h-full object-cover">
                            </div>

                            <p class="text-sm text-gray-600 mb-4">Tải lên ảnh đại diện mới</p>

                            <label for="avatar-file-input"
                                class="bg-blue-100 text-blue-600 px-4 py-2 rounded-md font-medium hover:bg-blue-200 cursor-pointer transition">
                                <i class="fas fa-upload mr-2"></i> Chọn ảnh
                            </label>
                            <form id="avatar-modal-form" action="{{ route('front.upload.avatar') }}" method="POST"
                                enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="photo" id="avatar-file-input" accept="image/*">
                            </form>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-avatar-btn"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100">
                            Huỷ bỏ
                        </button>
                        <button type="button" id="save-avatar-btn"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner Update Modal -->
    <div id="banner-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Cập nhật ảnh bìa</h3>
                        <button type="button" id="close-banner-modal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="mb-6">
                        <div class="flex flex-col items-center">
                            <div class="relative w-full h-48 bg-gray-100 rounded-lg overflow-hidden mb-4">
                                <div id="banner-preview-modal" class="w-full h-full bg-cover bg-center"
                                    style="background-image: url('{{ $profile->banner ? (strpos($profile->banner, 'http') === 0 ? $profile->banner : asset($profile->banner)) : asset('images/default-banner.jpg') }}');">
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mb-4">Tải lên ảnh bìa mới</p>

                            <label for="banner-file-input"
                                class="bg-blue-100 text-blue-600 px-4 py-2 rounded-md font-medium hover:bg-blue-200 cursor-pointer transition">
                                <i class="fas fa-upload mr-2"></i> Chọn ảnh
                            </label>
                            <form id="banner-modal-form" action="{{ route('front.upload.banner') }}" method="POST"
                                enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="banner" id="banner-file-input" accept="image/*">
                            </form>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-banner-btn"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100">
                            Huỷ bỏ
                        </button>
                        <button type="button" id="save-banner-btn"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div id="settings-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Cài đặt riêng tư</h3>
                        <button type="button" id="close-settings-modal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="privacy-settings-form" method="POST" action="{{ route('front.profile.privacy') }}">
                        @csrf
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                <div>
                                    <h4 class="font-medium text-gray-800">Chặn xem bài viết đã đăng</h4>
                                    <p class="text-sm text-gray-500">Không cho phép người khác xem bài viết bạn đã đăng</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="hide_posts" class="sr-only peer"
                                        {{ isset($privacySettings) && $privacySettings->hide_posts ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4 border-b border-gray-200">
                                <div>
                                    <h4 class="font-medium text-gray-800">Chặn xem thông tin cá nhân</h4>
                                    <p class="text-sm text-gray-500">Ẩn thông tin cá nhân của bạn với người khác</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="hide_personal_info" class="sr-only peer"
                                        {{ isset($privacySettings) && $privacySettings->hide_personal_info ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4 border-b border-gray-200">
                                <div>
                                    <h4 class="font-medium text-gray-800">Chặn xem sách đã đăng</h4>
                                    <p class="text-sm text-gray-500">Không cho phép người khác xem sách của bạn</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="hide_books" class="sr-only peer"
                                        {{ isset($privacySettings) && $privacySettings->hide_books ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <h4 class="font-medium text-gray-800">Chặn xem nội dung yêu thích</h4>
                                    <p class="text-sm text-gray-500">Ẩn sách và bài viết yêu thích của bạn</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="hide_favorites" class="sr-only peer"
                                        {{ isset($privacySettings) && $privacySettings->hide_favorites ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-8">
                            <button type="button" id="cancel-settings-btn"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100">
                                Huỷ bỏ
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Lưu cài đặt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="change-password-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Đổi mật khẩu</h3>
                        <button type="button" id="close-password-modal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Hiển thị thông báo lỗi nếu có -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-md">
                            <div class="text-red-600 font-medium">Có lỗi xảy ra:</div>
                            <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Hiển thị thông báo thành công nếu có -->
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-md">
                            <div class="text-green-600">{{ session('success') }}</div>
                        </div>
                    @endif

                    <form id="change-password-form" method="POST" action="{{ route('front.profile.changepass') }}">
                        @csrf
                        <div class="space-y-4">
                            <div class="mb-4">
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mật
                                    khẩu hiện tại</label>
                                <input type="password" id="current_password" name="current_password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('current_password') border-red-500 @enderror"
                                    required>
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu
                                    mới</label>
                                <input type="password" id="new_password" name="new_password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('new_password') border-red-500 @enderror"
                                    required>
                                <p class="text-xs text-gray-500 mt-1">Mật khẩu phải có ít nhất 8 ký tự</p>
                                @error('new_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="new_password_confirmation"
                                    class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-8">
                            <button type="button" id="cancel-password-btn"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100">
                                Huỷ bỏ
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ----------------------
            // Tab Switching Logic
            // ----------------------
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            const tabLinks = document.querySelectorAll('.tab-link');

            function activateTab(tabId) {
                // Deactivate all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.classList.remove('border-blue-500', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-600');
                });

                tabContents.forEach(content => {
                    content.classList.remove('active');
                });

                // Activate the selected tab
                const selectedButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
                if (selectedButton) {
                    selectedButton.classList.add('active', 'border-blue-500', 'text-blue-600');
                    selectedButton.classList.remove('border-transparent', 'text-gray-600');
                }

                const selectedContent = document.getElementById(tabId);
                if (selectedContent) {
                    selectedContent.classList.add('active');
                }

                // Update URL hash for bookmarking
                window.history.pushState(null, null, `#${tabId}`);
            }

            // Tab button click event
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    activateTab(tabId);
                });
            });

            // Tab link click event (from sidebar)
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('data-tab');
                    activateTab(tabId);
                });
            });

            // Check URL hash for tab
            if (window.location.hash) {
                const tabId = window.location.hash.substring(1);
                activateTab(tabId);
            } else {
                // Mặc định kích hoạt tab đầu tiên (bài viết đã đăng)
                activateTab('posts');
            }

            // ----------------------
            // Inline Editing Feature
            // ----------------------
            const editInfoBtn = document.getElementById('edit-info-btn');
            const cancelEditInfoBtn = document.getElementById('cancel-edit-info-btn');
            const infoViewMode = document.getElementById('info-view-mode');
            const infoEditMode = document.getElementById('info-edit-mode');
            const cancelEditFormBtn = document.getElementById('cancel-edit-form-btn');
            const profileEditBtn = document.getElementById('profile-edit-btn');
            const cancelInfoBtn = document.getElementById('cancel-info-btn');

            function switchToEditMode() {
                if (infoViewMode && infoEditMode) {
                    // Thêm hiệu ứng fade-out cho view mode
                    infoViewMode.style.opacity = '0';

                    setTimeout(() => {
                        infoViewMode.classList.add('hidden');
                        infoEditMode.classList.remove('hidden');

                        // Hiệu ứng fade-in cho edit mode
                        infoEditMode.style.opacity = '0';
                        setTimeout(() => {
                            infoEditMode.style.opacity = '1';
                        }, 50);

                        editInfoBtn.classList.add('hidden');
                        cancelEditInfoBtn.classList.remove('hidden');
                    }, 200);

                    // Activate personal info tab and scroll to it
                    activateTab('personal-info');
                    document.getElementById('personal-info').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }

            function switchToViewMode() {
                if (infoViewMode && infoEditMode) {
                    // Thêm hiệu ứng fade-out cho edit mode
                    infoEditMode.style.opacity = '0';

                    setTimeout(() => {
                        infoEditMode.classList.add('hidden');
                        infoViewMode.classList.remove('hidden');

                        // Hiệu ứng fade-in cho view mode
                        infoViewMode.style.opacity = '0';
                        setTimeout(() => {
                            infoViewMode.style.opacity = '1';
                        }, 50);

                        editInfoBtn.classList.remove('hidden');
                        cancelEditInfoBtn.classList.add('hidden');
                    }, 200);
                }
            }

            // Main edit button click
            if (profileEditBtn) {
                profileEditBtn.addEventListener('click', function() {
                    // Ensure the personal info tab is active
                    activateTab('personal-info');
                    // Switch to edit mode
                    switchToEditMode();
                });
            }

            // Edit info button click
            if (editInfoBtn) {
                editInfoBtn.addEventListener('click', switchToEditMode);
            }

            // Cancel edit buttons click
            if (cancelEditInfoBtn) {
                cancelEditInfoBtn.addEventListener('click', switchToViewMode);
            }

            if (cancelEditFormBtn) {
                cancelEditFormBtn.addEventListener('click', switchToViewMode);
            }

            // Cancel button trong form
            if (cancelInfoBtn) {
                cancelInfoBtn.addEventListener('click', switchToViewMode);
            }

            // ----------------------
            // Xử lý nút Like và Bookmark
            // ----------------------
            
            // Xử lý nút Like
            const likeButtons = document.querySelectorAll('[id^="like-btn-"]');
            likeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    const itemCode = this.getAttribute('data-item-code');
                    const isLiked = this.classList.contains('liked');
                    
                    // Gửi yêu cầu AJAX để like/unlike
                    fetch('{{ route('front.tblog.like') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            item_id: itemId,
                            item_code: itemCode
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Cập nhật UI
                        const likeIcon = this.querySelector('i');
                        const likeCountElement = document.getElementById(`like-count-${itemId}`);
                        
                        if (data.status === 'added') {
                            // Đã thích
                            this.classList.add('liked');
                            this.classList.add('text-blue-500');
                            likeIcon.classList.remove('far');
                            likeIcon.classList.add('fas');
                            
                            // Tăng số like
                            if (likeCountElement) {
                                const currentLikes = parseInt(likeCountElement.textContent || '0');
                                likeCountElement.textContent = currentLikes + 1;
                            }
                        } else {
                            // Đã bỏ thích
                            this.classList.remove('liked');
                            this.classList.remove('text-blue-500');
                            likeIcon.classList.remove('fas');
                            likeIcon.classList.add('far');
                            
                            // Giảm số like
                            if (likeCountElement) {
                                const currentLikes = parseInt(likeCountElement.textContent || '0');
                                likeCountElement.textContent = Math.max(0, currentLikes - 1);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi:', error);
                        alert('Đã xảy ra lỗi khi thích bài viết. Vui lòng thử lại sau.');
                    });
                });
            });
            
            // Xử lý nút bookmark (yêu thích)
            window.toggleBookmark = function(postId, itemCode) {
                fetch('{{ route('front.tblog.bookmark') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        item_id: postId,
                        item_code: itemCode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Cập nhật UI
                    const bookmarkBtn = document.getElementById(`bookmark-btn-${postId}`);
                    if (bookmarkBtn) {
                        const bookmarkIcon = bookmarkBtn.querySelector('i');
                        
                        if (data.status === 'added') {
                            bookmarkBtn.classList.add('text-red-500');
                            if (bookmarkIcon) {
                                bookmarkIcon.classList.remove('far');
                                bookmarkIcon.classList.add('fas');
                            }
                        } else {
                            bookmarkBtn.classList.remove('text-red-500');
                            if (bookmarkIcon) {
                                bookmarkIcon.classList.remove('fas');
                                bookmarkIcon.classList.add('far');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi yêu thích bài viết. Vui lòng thử lại sau.');
                });
            };

            // Xử lý form cập nhật thông tin cá nhân
            const profileInfoForm = document.getElementById('profile-info-form');
            if (profileInfoForm) {
                profileInfoForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');

                    // Disable nút submit và hiển thị trạng thái loading
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';
                    }

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Lỗi mạng hoặc lỗi máy chủ');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status) {
                                // Hiển thị thông báo thành công
                                alert(data.message || 'Đã cập nhật thông tin thành công!');
                                // Chuyển về chế độ xem
                                switchToViewMode();
                                // Cập nhật thông tin hiển thị trên trang
                                updateDisplayInfo(data.data);
                            } else {
                                throw new Error(data.message ||
                                    'Có lỗi xảy ra khi cập nhật thông tin.');
                            }
                        })
                        .catch(error => {
                            alert(error.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                            console.error('Error:', error);
                        })
                        .finally(() => {
                            // Kích hoạt lại nút submit
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Lưu thay đổi';
                            }
                        });
                });
            }

            // Hàm cập nhật thông tin hiển thị trên trang
            function updateDisplayInfo(data) {
                // Cập nhật thông tin hiển thị
                if (data.full_name) {
                    const fullNameElements = document.querySelectorAll('.profile-full-name');
                    fullNameElements.forEach(el => {
                        el.textContent = data.full_name;
                    });

                    // Cập nhật phần view mode
                    const fullNameView = document.querySelector('#info-view-mode p:nth-of-type(1)');
                    if (fullNameView) fullNameView.textContent = data.full_name;

                    // Cập nhật tên trên banner
                    const profileName = document.querySelector('.profile-cover h1');
                    if (profileName) {
                        profileName.textContent = data.full_name;
                        // Thêm hiệu ứng highlight ngắn
                        profileName.classList.add('text-highlight');
                        setTimeout(() => {
                            profileName.classList.remove('text-highlight');
                        }, 1500);
                    }
                }

                if (data.address) {
                    const addressElements = document.querySelectorAll('.profile-address');
                    addressElements.forEach(el => {
                        el.textContent = data.address;
                    });

                    // Cập nhật phần view mode
                    const addressView = document.querySelector('#info-view-mode p:nth-of-type(3)');
                    if (addressView) addressView.textContent = data.address;

                    // Cập nhật địa chỉ trên banner
                    const profileAddress = document.querySelector('.profile-cover span i.fa-map-marker-alt')
                        .parentNode;
                    if (profileAddress) {
                        // Lấy icon hiện tại
                        const icon = profileAddress.querySelector('i');
                        // Cập nhật nội dung
                        profileAddress.innerHTML = '';
                        profileAddress.appendChild(icon);
                        profileAddress.append(' ' + data.address);

                        // Thêm hiệu ứng highlight
                        profileAddress.classList.add('text-highlight');
                        setTimeout(() => {
                            profileAddress.classList.remove('text-highlight');
                        }, 1500);
                    }
                }

                if (data.description) {
                    const descriptionView = document.querySelector('#info-view-mode .whitespace-pre-line');
                    if (descriptionView) {
                        descriptionView.textContent = data.description;

                        // Thêm hiệu ứng highlight
                        descriptionView.classList.add('text-highlight');
                        setTimeout(() => {
                            descriptionView.classList.remove('text-highlight');
                        }, 1500);
                    }

                    // Cập nhật mô tả trên banner nếu có
                    const profileDesc = document.querySelector('.profile-cover > .container p');
                    if (profileDesc) {
                        profileDesc.textContent = data.description;
                    }
                }

                if (data.phone) {
                    const phoneView = document.querySelector('#info-view-mode p:nth-of-type(4)');
                    if (phoneView) {
                        phoneView.textContent = data.phone;

                        // Thêm hiệu ứng highlight
                        phoneView.classList.add('text-highlight');
                        setTimeout(() => {
                            phoneView.classList.remove('text-highlight');
                        }, 1500);
                    }
                }

                // Thêm thông báo thành công dưới dạng toast message
                showToast('Thông tin đã được cập nhật thành công', 'success');
            }

            // Hàm hiển thị thông báo toast
            function showToast(message, type = 'info') {
                // Kiểm tra nếu container toast đã tồn tại
                let toastContainer = document.getElementById('toast-container');

                if (!toastContainer) {
                    // Tạo container cho toast nếu chưa có
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toast-container';
                    toastContainer.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 9999;
                    `;
                    document.body.appendChild(toastContainer);
                }

                // Tạo toast message
                const toast = document.createElement('div');

                // Tạo style dựa vào loại thông báo
                let bgColor = 'bg-blue-500';
                let icon = 'info-circle';

                if (type === 'success') {
                    bgColor = 'bg-green-500';
                    icon = 'check-circle';
                } else if (type === 'error') {
                    bgColor = 'bg-red-500';
                    icon = 'exclamation-circle';
                } else if (type === 'warning') {
                    bgColor = 'bg-yellow-500';
                    icon = 'exclamation-triangle';
                }

                toast.className =
                    `toast ${bgColor} text-white px-4 py-3 rounded shadow-lg flex items-center mb-3 transform translate-x-full transition-transform duration-300`;
                toast.innerHTML = `
                    <i class="fas fa-${icon} mr-2"></i>
                    <span>${message}</span>
                    <button class="ml-auto text-white focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                // Thêm toast vào container
                toastContainer.appendChild(toast);

                // Hiển thị toast (sau 10ms để đảm bảo hiệu ứng chuyển động)
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 10);

                // Xử lý đóng toast khi nhấp vào nút đóng
                const closeBtn = toast.querySelector('button');
                closeBtn.addEventListener('click', () => {
                    closeToast(toast);
                });

                // Tự động đóng toast sau 3 giây
                setTimeout(() => {
                    closeToast(toast);
                }, 3000);
            }

            // Hàm đóng toast
            function closeToast(toast) {
                // Thêm hiệu ứng fade-out
                toast.classList.add('translate-x-full');

                // Xóa toast sau khi hiệu ứng hoàn tất
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }

            // ----------------------
            // Avatar và Banner Modal
            // ----------------------
            // Avatar Modal Controls
            const editAvatarBtn = document.getElementById('edit-avatar-btn');
            const avatarModal = document.getElementById('avatar-modal');
            const closeAvatarModal = document.getElementById('close-avatar-modal');
            const cancelAvatarBtn = document.getElementById('cancel-avatar-btn');
            const saveAvatarBtn = document.getElementById('save-avatar-btn');
            const avatarFileInput = document.getElementById('avatar-file-input');
            const avatarPreviewModal = document.getElementById('avatar-preview-modal');

            // Banner Modal Controls
            const editBannerBtn = document.getElementById('edit-banner-btn');
            const bannerModal = document.getElementById('banner-modal');
            const closeBannerModal = document.getElementById('close-banner-modal');
            const cancelBannerBtn = document.getElementById('cancel-banner-btn');
            const saveBannerBtn = document.getElementById('save-banner-btn');
            const bannerFileInput = document.getElementById('banner-file-input');
            const bannerPreviewModal = document.getElementById('banner-preview-modal');

            // Avatar Modal Events
            if (editAvatarBtn && avatarModal) {
                // Open Avatar Modal
                editAvatarBtn.addEventListener('click', function() {
                    avatarModal.classList.remove('hidden');
                });

                // Close Avatar Modal
                const closeAvatarModalFn = function() {
                    avatarModal.classList.add('hidden');
                    // Reset file input
                    if (avatarFileInput) avatarFileInput.value = '';
                };

                if (closeAvatarModal) closeAvatarModal.addEventListener('click', closeAvatarModalFn);
                if (cancelAvatarBtn) cancelAvatarBtn.addEventListener('click', closeAvatarModalFn);

                // Close on outside click
                avatarModal.addEventListener('click', function(e) {
                    if (e.target === avatarModal) {
                        closeAvatarModalFn();
                    }
                });
            }

            // Banner Modal Events
            if (editBannerBtn && bannerModal) {
                // Open Banner Modal
                editBannerBtn.addEventListener('click', function() {
                    bannerModal.classList.remove('hidden');
                });

                // Close Banner Modal
                const closeBannerModalFn = function() {
                    bannerModal.classList.add('hidden');
                    // Reset file input
                    if (bannerFileInput) bannerFileInput.value = '';
                };

                if (closeBannerModal) closeBannerModal.addEventListener('click', closeBannerModalFn);
                if (cancelBannerBtn) cancelBannerBtn.addEventListener('click', closeBannerModalFn);

                // Close on outside click
                bannerModal.addEventListener('click', function(e) {
                    if (e.target === bannerModal) {
                        closeBannerModalFn();
                    }
                });
            }

            // Avatar Preview Update
            if (avatarFileInput && avatarPreviewModal) {
                avatarFileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            avatarPreviewModal.src = e.target.result;
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Banner Preview Update
            if (bannerFileInput && bannerPreviewModal) {
                bannerFileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            bannerPreviewModal.style.backgroundImage = `url('${e.target.result}')`;
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Save Avatar
            if (saveAvatarBtn) {
                saveAvatarBtn.addEventListener('click', async function() {
                    if (!avatarFileInput.files || !avatarFileInput.files[0]) {
                        alert('Vui lòng chọn ảnh để tải lên');
                        return;
                    }

                    const avatarForm = document.getElementById('avatar-modal-form');
                    const formData = new FormData();
                    formData.append('photo', avatarFileInput.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        saveAvatarBtn.disabled = true;
                        saveAvatarBtn.innerHTML = 'Đang lưu...';

                        console.log('Uploading avatar...', avatarFileInput.files[0]);

                        const response = await fetch('{{ route('front.upload.avatar') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();
                        console.log('Avatar response:', data);

                        if (!response.ok || !data.status) {
                            throw new Error(data.message || 'Lỗi khi tải lên avatar');
                        }

                        if (data.url) {
                            console.log('Cập nhật ảnh đại diện vào CSDL:', data.url);
                            // Cập nhật avatar vào CSDL sau khi tải lên
                            const updateResult = await updateProfileImage('photo', data.url);
                            console.log('Kết quả cập nhật:', updateResult);

                            // Đóng modal và làm mới trang
                            avatarModal.classList.add('hidden');
                            window.location.reload();
                        } else {
                            throw new Error('Không nhận được URL ảnh từ server');
                        }
                    } catch (error) {
                        console.error('Lỗi:', error);
                        alert('Có lỗi xảy ra: ' + error.message);
                    } finally {
                        saveAvatarBtn.disabled = false;
                        saveAvatarBtn.innerHTML = 'Lưu thay đổi';
                    }
                });
            }

            // Save Banner
            if (saveBannerBtn) {
                saveBannerBtn.addEventListener('click', async function() {
                    if (!bannerFileInput.files || !bannerFileInput.files[0]) {
                        alert('Vui lòng chọn ảnh để tải lên');
                        return;
                    }

                    const bannerForm = document.getElementById('banner-modal-form');
                    const formData = new FormData();
                    formData.append('banner', bannerFileInput.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        saveBannerBtn.disabled = true;
                        saveBannerBtn.innerHTML = 'Đang lưu...';

                        console.log('Uploading banner...', bannerFileInput.files[0]);

                        const response = await fetch('{{ route('front.upload.banner') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();
                        console.log('Banner response:', data);

                        if (!response.ok || !data.status) {
                            throw new Error(data.message || 'Lỗi khi tải lên ảnh bìa');
                        }

                        if (data.url) {
                            console.log('Cập nhật ảnh bìa vào CSDL:', data.url);
                            // Cập nhật banner vào CSDL sau khi tải lên
                            const updateResult = await updateProfileImage('banner', data.url);
                            console.log('Kết quả cập nhật:', updateResult);

                            // Đóng modal và làm mới trang
                            bannerModal.classList.add('hidden');
                            window.location.reload();
                        } else {
                            throw new Error('Không nhận được URL ảnh từ server');
                        }
                    } catch (error) {
                        console.error('Lỗi:', error);
                        alert('Có lỗi xảy ra: ' + error.message);
                    } finally {
                        saveBannerBtn.disabled = false;
                        saveBannerBtn.innerHTML = 'Lưu thay đổi';
                    }
                });
            }

            // Hàm cập nhật ảnh vào profile
            async function updateProfileImage(fieldName, fieldValue) {
                try {
                    console.log(`Đang cập nhật ${fieldName} với giá trị:`, fieldValue);

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append(fieldName, fieldValue);

                    const response = await fetch('{{ route('front.profile.updatefield') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const result = await response.json();
                    console.log('Kết quả cập nhật:', result);

                    if (!response.ok) {
                        throw new Error('Lỗi khi cập nhật ảnh: ' + (result.message || 'Không xác định'));
                    }

                    if (!result.status) {
                        throw new Error(result.message || 'Lỗi không xác định khi cập nhật ảnh');
                    }

                    return result;
                } catch (error) {
                    console.error('Lỗi cập nhật ảnh:', error);
                    throw error;
                }
            }

            // ----------------------
            // Mobile features
            // ----------------------
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-profile-menu');
            const mobileMenuDropdown = document.getElementById('mobile-profile-dropdown');

            if (mobileMenuButton && mobileMenuDropdown) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenuDropdown.classList.toggle('hidden');
                });
            }

            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const leftSidebar = document.getElementById('left-sidebar');
            const mainContent = document.getElementById('main-content');

            if (sidebarToggle && leftSidebar && mainContent) {
                sidebarToggle.addEventListener('click', function() {
                    leftSidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }

            // Form handling
            const profileEditForm = document.getElementById('profile-edit-form');
            if (profileEditForm) {
                profileEditForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');

                    // Thêm các trường ẩn cần thiết nếu chưa có
                    if (!formData.has('photo_old') && document.getElementById('profile-image-preview')) {
                        const currentAvatar = document.getElementById('profile-image-preview').getAttribute(
                            'src');
                        formData.append('photo_old', currentAvatar);
                    }

                    if (!formData.has('banner_old') && document.querySelector('.profile-cover')) {
                        // Lấy banner từ thuộc tính background-image hoặc src của banner
                        const bannerImg = document.querySelector('.profile-cover img');
                        if (bannerImg) {
                            formData.append('banner_old', bannerImg.getAttribute('src'));
                        }
                    }

                    // Disable the submit button and show loading state
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';
                    }

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Lỗi mạng hoặc lỗi máy chủ');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                alert(data.message || 'Cập nhật thông tin thành công!');
                                // Reload the page to show updated info
                                window.location.reload();
                            } else {
                                throw new Error(data.message ||
                                    'Có lỗi xảy ra khi cập nhật thông tin.');
                            }
                        })
                        .catch(error => {
                            alert(error.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                            console.error('Error:', error);

                            // Re-enable the submit button
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Lưu thay đổi';
                            }
                        });
                });
            }

            // Privacy Settings Modal
            const settingsBtn = document.getElementById('settings-btn');
            const settingsBtnMobile = document.getElementById('settings-btn-mobile');
            const settingsModal = document.getElementById('settings-modal');
            const closeSettingsModal = document.getElementById('close-settings-modal');
            const cancelSettingsBtn = document.getElementById('cancel-settings-btn');

            function openSettingsModal() {
                settingsModal.classList.remove('hidden');
            }

            function closeSettingsModalFn() {
                settingsModal.classList.add('hidden');
            }

            if (settingsBtn) {
                settingsBtn.addEventListener('click', openSettingsModal);
            }

            if (settingsBtnMobile) {
                settingsBtnMobile.addEventListener('click', openSettingsModal);
            }

            if (closeSettingsModal) {
                closeSettingsModal.addEventListener('click', closeSettingsModalFn);
            }

            if (cancelSettingsBtn) {
                cancelSettingsBtn.addEventListener('click', closeSettingsModalFn);
            }

            // Close on outside click
            if (settingsModal) {
                settingsModal.addEventListener('click', function(e) {
                    if (e.target === settingsModal) {
                        closeSettingsModalFn();
                    }
                });
            }

            // Form handling for privacy settings
            const privacySettingsForm = document.getElementById('privacy-settings-form');
            if (privacySettingsForm) {
                privacySettingsForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');

                    // Prepare form data - make sure checkboxes are properly handled
                    const settingsData = {
                        hide_posts: formData.has('hide_posts'),
                        hide_personal_info: formData.has('hide_personal_info'),
                        hide_books: formData.has('hide_books'),
                        hide_favorites: formData.has('hide_favorites'),
                        _token: formData.get('_token')
                    };

                    // Disable the submit button and show loading state
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';
                    }

                    fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': formData.get('_token'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(settingsData)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Lỗi mạng hoặc lỗi máy chủ');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                alert(data.message || 'Cài đặt riêng tư đã được cập nhật!');
                                // Close modal
                                closeSettingsModalFn();
                            } else {
                                throw new Error(data.message || 'Có lỗi xảy ra khi cập nhật cài đặt.');
                            }
                        })
                        .catch(error => {
                            alert(error.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                            console.error('Error:', error);
                        })
                        .finally(() => {
                            // Re-enable the submit button
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Lưu cài đặt';
                            }
                        });
                });
            }

            // Toggle bookmark và cập nhật UI
            function toggleBookmark(postId, itemCode) {
                fetch('{{ route('front.tblog.bookmark') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            item_id: postId,
                            item_code: itemCode
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Cập nhật UI
                        const bookmarkBtn = document.getElementById(`bookmark-btn-${postId}`);
                        const bookmarkIcon = bookmarkBtn.querySelector('i');

                        if (data.status === 'added') {
                            bookmarkBtn.classList.add('text-red-500');
                            bookmarkIcon.classList.remove('far');
                            bookmarkIcon.classList.add('fas');
                        } else {
                            bookmarkBtn.classList.remove('text-red-500');
                            bookmarkIcon.classList.remove('fas');
                            bookmarkIcon.classList.add('far');
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi:', error);
                        alert('Đã xảy ra lỗi khi yêu thích bài viết. Vui lòng thử lại sau.');
                    });
            }
        });

        // Xử lý modal đổi mật khẩu
        document.addEventListener('DOMContentLoaded', function() {
            const changePasswordBtn = document.getElementById('change-password-btn');
            const passwordModal = document.getElementById('change-password-modal');
            const closePasswordModal = document.getElementById('close-password-modal');
            const cancelPasswordBtn = document.getElementById('cancel-password-btn');

            // Mở modal
            if (changePasswordBtn) {
                changePasswordBtn.addEventListener('click', function() {
                    passwordModal.classList.remove('hidden');
                });
            }

            // Đóng modal
            function closePasswordModalFn() {
                passwordModal.classList.add('hidden');
                // Reset form
                document.getElementById('change-password-form').reset();
            }

            if (closePasswordModal) {
                closePasswordModal.addEventListener('click', closePasswordModalFn);
            }

            if (cancelPasswordBtn) {
                cancelPasswordBtn.addEventListener('click', closePasswordModalFn);
            }

            // Đóng khi click bên ngoài
            if (passwordModal) {
                passwordModal.addEventListener('click', function(e) {
                    if (e.target === passwordModal) {
                        closePasswordModalFn();
                    }
                });
            }

            // Xử lý form đổi mật khẩu
            const changePasswordForm = document.getElementById('change-password-form');
            if (changePasswordForm) {
                changePasswordForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Kiểm tra mật khẩu mới và xác nhận mật khẩu mới có khớp nhau không
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('new_password_confirmation').value;

                    if (newPassword !== confirmPassword) {
                        alert('Mật khẩu xác nhận không khớp với mật khẩu mới!');
                        return false;
                    }

                    // Kiểm tra độ dài mật khẩu
                    if (newPassword.length < 8) {
                        alert('Mật khẩu mới phải có ít nhất 8 ký tự!');
                        return false;
                    }

                    // Vô hiệu hóa nút gửi và hiển thị trạng thái loading
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý...';
                    }

                    // Gửi form bằng AJAX
                    const formData = new FormData(this);

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Lỗi mạng hoặc lỗi máy chủ');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Hiển thị thông báo thành công
                                alert(data.message || 'Đổi mật khẩu thành công!');

                                // Đóng modal và reset form
                                closePasswordModalFn();

                                // Tạo một thông báo toast đơn giản
                                const toastElement = document.createElement('div');
                                toastElement.className =
                                    'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                                toastElement.textContent = 'Đổi mật khẩu thành công!';
                                document.body.appendChild(toastElement);

                                // Tự động xóa sau 3 giây
                                setTimeout(() => {
                                    if (toastElement.parentNode) {
                                        toastElement.parentNode.removeChild(toastElement);
                                    }
                                }, 3000);
                            } else {
                                throw new Error(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert(error.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                        })
                        .finally(() => {
                            // Kích hoạt lại nút submit
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Đổi mật khẩu';
                            }
                        });
                });
            }

            // Kiểm tra nếu có lỗi từ server khi gửi form
            @if ($errors->any())
                passwordModal.classList.remove('hidden'); // Hiển thị lại modal nếu có lỗi
            @endif

            // Xử lý nút bookmark và xóa sách
            document.querySelectorAll('.bookmark-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    const itemCode = this.getAttribute('data-item-code');

                    if (!confirm('Bạn có chắc muốn bỏ sách này khỏi danh sách yêu thích?')) {
                        return;
                    }

                    fetch('{{ route('front.book.bookmark') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                item_id: itemId,
                                item_code: itemCode
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Tìm và xóa phần tử cha chứa sách khỏi giao diện
                                const bookElement = this.closest('.bg-white.border');
                                if (bookElement) {
                                    bookElement.remove();
                                }
                            } else {
                                alert('Có lỗi xảy ra: ' + (data.msg ||
                                    'Không thể bỏ yêu thích'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Có lỗi xảy ra khi thực hiện thao tác.');
                        });
                });
            });

            // Xử lý form xóa sách
            document.querySelectorAll('.delete-book-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (confirm(
                            'Bạn có chắc muốn xóa sách này? Hành động này không thể hoàn tác.')) {
                        this.submit();
                    }
                });
            });
        });
    </script>
    <script src="{{ asset('modules/tuongtac/social-interactions.js') }}"></script>
    @stack('scripts')
@endsection
