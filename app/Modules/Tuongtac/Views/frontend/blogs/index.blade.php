@extends('Tuongtac::frontend.blogs.body')
<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

$adsense_code = '<ins class="adsbygoogle"
            style="display:block; text-align:center;"
            data-ad-layout="in-article"
            data-ad-format="fluid"
            data-ad-client="ca-pub-5437344106154965"
            data-ad-slot="3375673265"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>';

?>
@section('topcss')
    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

    <style>
        /* Biến cho toàn bộ giao diện */
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --text-dark: #333;
            --text-light: #666;
            --text-lighter: #888;
            --bg-light: #f9fafb;
            --bg-white: #fff;
            --border-light: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-full: 9999px;
        }

        /* Post Card */
        .blog-post-card {
            background-color: var(--bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            padding: 1.25rem;
            transition: all 0.2s ease;
        }

        .blog-post-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Search Bar */
        .search-container {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            margin-bottom: 16px;
        }

        .search-form {
            flex-grow: 1;
        }

        .search-input-container {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-full);
            background-color: var(--bg-white);
            padding: 0 12px;
            width: 100%;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .search-input-container:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.2);
        }

        .search-input {
            border: none;
            background: transparent;
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
            color: var(--text-dark);
        }

        .search-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-full);
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .search-button:hover {
            background-color: var(--primary-hover);
        }

        /* Create Post Button */
        .create-post-button {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            box-shadow: var(--shadow-sm);
        }

        .create-post-button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Post Header */
        .post-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .avatar-img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid white;
            box-shadow: var(--shadow-sm);
        }

        .post-meta {
            flex: 1;
        }

        .post-author {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .post-time {
            font-size: 12px;
            color: var(--text-lighter);
        }

        /* Post Content */
        .post-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .post-title a:hover {
            color: var(--primary-color);
            text-decoration: none;
        }

        .post-summary {
            font-size: 15px;
            line-height: 1.5;
            color: var(--text-light);
            margin-bottom: 12px;
        }

        .post-image {
            width: 100%;
            height: auto;
            border-radius: var(--radius-md);
            margin-top: 8px;
            margin-bottom: 12px;
        }

        /* Tags */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 12px;
            gap: 8px;
        }

        .post-tag {
            display: inline-block;
            background-color: #e9f5fe;
            color: var(--primary-color);
            font-size: 12px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: var(--radius-full);
            transition: background-color 0.2s;
        }

        .post-tag:hover {
            background-color: #d1e9fd;
            text-decoration: none;
        }

        /* Post Stats */
        .post-stats {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 13px;
            color: var(--text-lighter);
            border-top: 1px solid var(--border-light);
            border-bottom: 1px solid var(--border-light);
            margin-bottom: 12px;
        }

        .stat-item {
            display: flex;
            align-items: center;
        }

        .stat-item i {
            margin-right: 4px;
        }

        /* Post Actions */
        .post-actions {
            display: flex;
            justify-content: space-between;
        }

        .post-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 8px 0;
            border-radius: var(--radius-md);
            background: transparent;
            color: var(--text-light);
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .post-action-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .post-action-btn i {
            margin-right: 6px;
        }

        .post-action-btn.active,
        .like-button.text-blue-600 {
            color: var(--primary-color);
        }

        /* Comment Section */
        .comment-input-container {
            display: flex;
            align-items: center;
            margin-top: 12px;
        }

        .comment-input {
            flex: 1;
            border: none;
            background-color: var(--bg-light);
            border-radius: var(--radius-full);
            padding: 10px 16px;
            font-size: 14px;
            outline: none;
        }

        .comment-action {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 8px;
        }

        .comment-action button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-lighter);
            transition: color 0.2s;
        }

        .comment-action button:hover {
            color: var(--primary-color);
        }

        /* Loading and Load More */
        .loading-spinner {
            display: none;
            justify-content: center;
            margin: 2rem 0;
        }

        .loading-spinner.active {
            display: flex;
        }

        .load-more-btn {
            display: inline-block;
            background-color: var(--bg-white);
            color: var(--text-dark);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .load-more-btn:hover {
            background-color: var(--bg-light);
            box-shadow: var(--shadow-md);
        }

        /* Dropzone styling */
        .dropzone {
            border: 2px dashed #0087F7;
            border-radius: var(--radius-md);
            background: white;
            min-height: 150px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .dropzone .dz-message {
            font-weight: 400;
            font-size: 16px;
            color: var(--text-lighter);
            text-align: center;
            margin: 2em 0;
        }

        /* Modal */
        .blog-modal {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .blog-modal.hidden {
            display: none;
        }

        .blog-modal-content {
            background: white;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .blog-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
        }

        .blog-modal-body {
            padding: 1.5rem;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
            }

            .search-form {
                width: 100%;
                margin-bottom: 10px;
            }

            .create-post-button {
                width: 100%;
            }
        }
    </style>
@endsection

@section('inner-content')
    <!-- Search and Create Post -->
    <div class="blog-post-card">
        <div class="search-container">
            <div class="search-form">
                <form action="{{ route('front.tblogs.index') }}" method="GET" class="search-input-container">
                    <i class="fas fa-search" style="color: #9ca3af;"></i>
                    <input type="text" name="search" placeholder="Tìm kiếm bài viết..." value="{{ request('search') }}"
                        class="search-input">
                    <button type="submit" class="search-button">Tìm</button>
                </form>
            </div>

            <a href="javascript:void(0);" onclick="openCreatePostModal()" class="create-post-button">
                <i class="fas fa-plus mr-2"></i> Tạo bài viết mới
            </a>
        </div>
    </div>

    <!-- Post Feed -->
    <section>
        <!-- Loading Spinner -->
        <div id="loading-spinner" class="loading-spinner">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <!-- Post Feed -->
        <div id="post-feed">
            <?php $vitri = 0; ?>

            @foreach ($posts as $post)
                <?php
                $images = json_decode($post->photo, true);
                if (!$images) {
                    $thumbnail_url = 'https://itcctv.vn/images/profile-8.jpg';
                } else {
                    $thumbnail_url = $images[0];
                }
                
                $createdAt = Carbon::parse($post->created_at);
                $diffInMinutes = $createdAt->diffInMinutes();
                $diffInHours = $createdAt->diffInHours();
                $diffInDays = $createdAt->diffInDays();
                $thoigian = '';
                
                if ($diffInMinutes < 60) {
                    $thoigian = round($diffInMinutes) . ' phút trước';
                } elseif ($diffInHours < 24) {
                    $thoigian = round($diffInHours) . ' tiếng trước';
                } else {
                    $thoigian = round($diffInDays) . ' ngày trước';
                }
                
                $vitri++;
                if ($vitri % 6 == 0) {
                    echo '<div class="blog-post-card">' . $adsense_code . '</div>';
                }
                ?>

                <div class="blog-post-card relative {{ $post->status == 0 ? 'bg-gray-50' : '' }}">
                    <!-- Action buttons -->
                    <div class="absolute top-3 right-3 z-10 flex space-x-2">
                        @if (\Auth::id() == ($post->author ? $post->author->id : null) || (auth()->id() && auth()->user()->role == 'admin'))
                            <form action="{{ route('front.tblogs.destroy', $post->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                                    title="Xóa bài viết" onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');">
                                    <i class="fas fa-trash-alt text-gray-600 text-sm"></i>
                                </button>
                            </form>

                            <a href="javascript:void(0);" onclick="openEditPostModal({{ $post->id }})"
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
                        @endif
                    </div>

                    <div class="post-header">
                        <a href="{{ route('front.user.profile', ['id' => $post->author ? $post->author->id : 0]) }}">
                            <img src="{{ $post->author ? $post->author->photo : '/images/default-avatar.png' }}"
                                alt="Avatar" class="avatar-img">
                        </a>
                        <div class="post-meta">
                            <h3 class="post-author">
                                <a href="{{ route('front.user.profile', ['id' => $post->author ? $post->author->id : 0]) }}"
                                    class="hover:text-blue-600">
                                    {{ $post->author ? $post->author->full_name : 'Người dùng không xác định' }}
                                </a>
                            </h3>
                            <p class="post-time">{{ $thoigian }} ·
                                <i class="fas fa-{{ $post->status == 1 ? 'globe-americas' : 'lock' }}"></i>
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
                            <div class="tags-container">
                                @foreach ($post->tags as $tag)
                                    <a href="{{ route('front.tblogs.tag', $tag->slug) }}" class="post-tag">
                                        #{{ $tag->title }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if ($images)
                            <img src="{{ $thumbnail_url }}" alt="{{ $post->title }}" class="post-image">
                        @endif
                    </div>

                    <div class="post-stats">
                        <div class="flex items-center">
                            <div class="stat-item">
                                <i class="fas fa-thumbs-up text-blue-500"></i>
                                <span id="like-count-{{ $post->id }}">{{ $post->likes_count ?? 0 }}</span>
                            </div>
                            <div class="stat-item ml-4">
                                <i class="fas fa-comment text-gray-400"></i>
                                <span>{{ $post->comment_count ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="text-xs">{{ $post->share_count ?? 0 }} lượt chia sẻ</div>
                    </div>

                    <!-- Nút tương tác -->
                    <div class="post-actions">
                        <button id="like-btn-{{ $post->id }}"
                            class="like-button post-action-btn {{ isset($post->user_has_liked) && $post->user_has_liked ? 'text-blue-600' : '' }}"
                            data-item-id="{{ $post->id }}" data-item-code="tblog">
                            <i
                                class="{{ isset($post->user_has_liked) && $post->user_has_liked ? 'fas' : 'far' }} fa-thumbs-up"></i>
                            Thích
                        </button>
                        <button onclick="toggleCommentBox({{ $post->id }}, 'tblog')" class="post-action-btn">
                            <i class="far fa-comment"></i> Bình luận
                        </button>
                        <button onclick="sharePost({{ $post->id }}, '{{ $post->slug }}', 'tblog')"
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
                            <input type="text" id="comment-input-{{ $post->id }}" style="width: 100%;"
                                placeholder="Viết bình luận..." class="comment-input">
                            <div class="comment-action">
                                <button class="emoji-trigger" onclick="addEmoji({{ $post->id }}, event, 'tblog')"
                                    data-item-id="{{ $post->id }}">
                                    <i class="far fa-smile"></i>
                                </button>
                                <button onclick="submitComment({{ $post->id }}, 'tblog')">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Comment Box -->
                    <div id="comment-box-{{ $post->id }}" class="mt-3 bg-white rounded-lg p-4"
                        style="display: none;">
                        <div id="comments-container-{{ $post->id }}">
                            <div class="text-center text-gray-500 text-sm py-2">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải bình luận...
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Load More Button -->
            @if (isset($posts) && count($posts) >= 10)
                <div class="text-center mt-6">
                    <button id="load-more" class="load-more-btn">
                        <i class="fas fa-sync-alt mr-2"></i> Tải thêm bài viết
                    </button>
                </div>
            @endif
        </div>
    </section>

    <!-- Thêm modal popup cho chỉnh sửa bài viết -->
    <div id="editPostModal" class="blog-modal hidden">
        <div class="blog-modal-content">
            <div class="blog-modal-header">
                <h2 class="text-xl font-bold">Chỉnh sửa bài viết</h2>
                <button onclick="closeEditPostModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="editPostContent" class="blog-modal-body">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal tạo bài viết mới -->
    <div id="createPostModal" class="blog-modal hidden">
        <div class="blog-modal-content">
            <div class="blog-modal-header">
                <h2 class="text-xl font-bold">Tạo bài viết mới</h2>
                <button onclick="closeCreatePostModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="createPostContent" class="blog-modal-body">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('botscript')
    <!-- Dropzone JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <!-- CKEditor -->
    <script src="{{ asset('js/js/ckeditor.js') }}"></script>

    <script>
        // Biến toàn cục để lưu trữ các instance của Dropzone
        var globalDropzones = {};

        // Đảm bảo Dropzone không tự động phát hiện
        Dropzone.autoDiscover = false;

        document.addEventListener('DOMContentLoaded', function() {
            // ... existing code ...
        });

        // Mở popup chỉnh sửa bài viết
        function openEditPostModal(postId) {
            const modal = document.getElementById('editPostModal');
            const content = document.getElementById('editPostContent');

            // Show modal with loading spinner
            modal.classList.remove('hidden');
            content.innerHTML =
                '<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div></div>';

            console.log(`Đang gửi request tới URL: /tblogs/${postId}/edit`);

            // Fetch form content from server với X-Requested-With header để đánh dấu Ajax request
            fetch(`/tblogs/${postId}/edit`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin' // Đảm bảo gửi cookies để xác thực session
                })
                .then(response => {
                    console.log('Nhận phản hồi:', response.status, response.statusText);

                    if (!response.ok) {
                        if (response.status === 401) {
                            window.location.href = '{{ route('front.login') }}';
                            throw new Error('Bạn cần đăng nhập để thực hiện chức năng này.');
                        }
                        if (response.status === 404) {
                            throw new Error(
                                'Không tìm thấy bài viết hoặc API không tồn tại. Vui lòng kiểm tra lại đường dẫn.');
                        }
                        return response.text().then(text => {
                            console.error('Nội dung phản hồi lỗi:', text);
                            try {
                                const json = JSON.parse(text);
                                throw new Error(json.error || 'Không thể tải form.');
                            } catch (e) {
                                throw new Error('Không thể tải form. Lỗi server: ' + response.status);
                            }
                        });
                    }

                    // Kiểm tra xem phản hồi có phải là JSON không
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Phản hồi không phải JSON:', contentType);
                        throw new Error('Phản hồi không hợp lệ từ server, không đúng định dạng JSON');
                    }

                    return response.json();
                })
                .then(data => {
                    console.log('Dữ liệu thành công:', data);
                    // Render form
                    renderEditForm(data, content, postId);
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    content.innerHTML = `
                    <div class="text-red-500 text-center p-4">
                        <p class="font-bold text-lg mb-2">Không thể tải form</p>
                        <p>${error.message}</p>
                        <div class="mt-4">
                            <button onclick="closeEditPostModal()" class="px-4 py-2 bg-gray-300 rounded-md">Đóng</button>
                            <a href="/tblogs/${postId}/edit" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md">Mở trang chỉnh sửa</a>
                        </div>
                    </div>`;
                });
        }

        function closeEditPostModal() {
            const modal = document.getElementById('editPostModal');
            modal.classList.add('hidden');

            // Hủy bỏ instance Dropzone nếu có
            if (globalDropzones['editDropzone']) {
                try {
                    console.log('Đang hủy Dropzone khi đóng modal chỉnh sửa...');
                    globalDropzones['editDropzone'].destroy();
                    console.log('Đã hủy Dropzone thành công');
                    delete globalDropzones['editDropzone'];
                } catch (error) {
                    console.error('Lỗi khi hủy Dropzone:', error);
                }
            }
        }

        // Function để hiện thị form chỉnh sửa
        function renderEditForm(data, contentElement, postId) {
            // Log dữ liệu nhận được
            console.log('Đang render form với data:', data);

            // Chuẩn bị dữ liệu ảnh từ post
            let imagesHTML = '';
            try {
                const images = JSON.parse(data.post.photo);
                if (images && images.length > 0) {
                    imagesHTML = `
                <div class="flex flex-wrap mt-2 image-previews">
                    ${images.map((photo, index) => photo ? `
                            <div class="image-preview mr-2 mb-2 relative">
                                <img class="rounded-md" style="width:80px; height:80px; object-fit: cover;" src="${photo}">
                                <button type="button" class="delete-image-btn absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center cursor-pointer hover:bg-red-600" data-photo="${photo}" data-index="${index}">×</button>
                            </div>
                            ` : '').join('')}
                </div>`;
                }
            } catch (e) {
                console.error('Lỗi xử lý ảnh:', e);
            }

            // Tạo form HTML
            const formHtml = `
            <div class="p-4">
                <form id="editPostForm" action="/tblogs/${postId}" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="${data.csrf_token}">
                    <input type="hidden" name="_method" value="PATCH">
                    
                    <!-- Upload ảnh đầu bài -->
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Upload hình ảnh</label>
                        <div class="dropzone border-2 border-dashed border-blue-400 rounded-lg p-4 bg-gray-50" id="editImageDropzone"></div>
                        <div id="editUploadStatus" class="mt-2 p-2 hidden"></div>
                    </div>
                    
                    <!-- Hiển thị ảnh đã tải lên trước đó -->
                    ${imagesHTML}
                    
                    <!-- Ẩn input để lưu tên file ảnh -->
                    <input type="hidden" name="photo" id="uploadedimages" value='${data.post.photo || "[]"}'>
                    
                    <!-- Tiêu đề bài viết -->
                    <div class="mb-4 mt-4">
                        <label class="block text-gray-700 mb-2">Tiêu đề</label>
                        <input type="text" name="title" value="${data.post.title}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <!-- Thẻ bài viết -->
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Tags</label>
                        <select id="edit-tags" name="tags[]" multiple class="w-full">
                            ${data.tags.map(tag => {
                                const isSelected = data.post.tags && data.post.tags.some(t => t.id === tag.id);
                                return `<option value="${tag.id}" ${isSelected ? 'selected' : ''}>${tag.title}</option>`;
                            }).join('')}
                        </select>
                        <span class="text-sm text-gray-500">Tối đa 5 tag</span>
                    </div>
                    
                    <!-- Nội dung bài viết -->
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Nội dung</label>
                        <textarea name="content" id="edit-content" rows="10" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">${data.post.content}</textarea>
                    </div>
                    
                    <!-- Tài liệu đính kèm -->
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Tài liệu</label>
                        <input type="file" name="document[]" id="edit-document" class="w-full px-3 py-2 border border-gray-300 rounded-md" multiple>
                        
                        ${data.post.documents && data.post.documents.length > 0 ? `
                                <div class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Tài liệu hiện tại:</p>
                                    <div class="space-y-2">
                                        ${data.post.documents.map((doc, index) => `
                                <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                    <div class="flex items-center flex-grow">
                                        <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                        <span class="text-sm mr-2">${doc.name || `Tài liệu ${index + 1}`}</span>
                                        <a href="${doc.url}" target="_blank" class="text-blue-500 hover:text-blue-700 ml-2 text-xs">
                                            <i class="fas fa-external-link-alt mr-1"></i>Xem
                                        </a>
                                    </div>
                                    <button type="button" class="text-red-500 hover:text-red-700 remove-document" data-id="${doc.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                `).join('')}
                                    </div>
                                </div>
                                ` : ''}
                        
                        <div class="mt-3">
                            <label class="block text-gray-700 mb-2">URL tài liệu</label>
                            <div id="url-fields">
                                ${data.post.urls && data.post.urls.length > 0 ? 
                                    data.post.urls.map((url, index) => `
                                            <div class="flex items-center mb-2 url-field">
                                                <input type="text" name="urls[]" value="${url}" class="flex-grow px-3 py-2 border border-gray-300 rounded-md">
                                                <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-url">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            `).join('') 
                                    : 
                                    `<input type="text" name="urls[]" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="URL file (nếu có)">`
                                }
                            </div>
                            <button type="button" id="add-url-field" class="mt-2 text-blue-500 hover:text-blue-700 text-sm">
                                <i class="fas fa-plus mr-1"></i> Thêm URL
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-4">
                        <button type="button" onclick="closeEditPostModal()" 
                            class="px-4 py-2 bg-gray-300 rounded-md mr-2">Hủy</button>
                        <button type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-md">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        `;

            // Gán HTML vào element
            contentElement.innerHTML = formHtml;

            // Hủy bỏ instance Dropzone hiện có nếu có
            if (globalDropzones['editDropzone']) {
                try {
                    globalDropzones['editDropzone'].destroy();
                    console.log('Đã hủy instance Dropzone cũ cho edit modal');
                } catch (e) {
                    console.error('Lỗi khi hủy instance Dropzone cũ:', e);
                }
            }

            // Khởi tạo Dropzone
            const uploadedimages = [];
            const uploadStatus = document.getElementById('editUploadStatus');

            // Khởi tạo từ dữ liệu hiện có
            try {
                if (data.post.photo) {
                    const images = JSON.parse(data.post.photo);
                    if (Array.isArray(images)) {
                        images.forEach(img => {
                            if (img) uploadedimages.push(img);
                        });
                    }
                }
            } catch (e) {
                console.error('Lỗi khi phân tích JSON ảnh:', e);
            }

            // Thêm delay nhỏ để đảm bảo DOM đã được cập nhật
            setTimeout(() => {
                console.log('Gắn sự kiện cho nút xóa ảnh...');
                // Gắn lại sự kiện cho các nút xóa ảnh sau khi HTML được chèn vào DOM
                const deleteButtons = document.querySelectorAll('.delete-image-btn');
                console.log('Tìm thấy', deleteButtons.length, 'nút xóa ảnh');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const photo = this.getAttribute('data-photo');
                        const parent = this.closest('.image-preview');

                        console.log('Đang xóa ảnh:', photo);

                        if (confirm('Bạn có chắc chắn muốn xóa ảnh này không?')) {
                            if (parent) {
                                parent.remove();
                            }

                            const index = uploadedimages.indexOf(photo);
                            if (index !== -1) {
                                uploadedimages.splice(index, 1);
                                document.getElementById('uploadedimages').value = JSON.stringify(
                                    uploadedimages);
                                console.log('Đã xóa ảnh:', photo);
                                console.log('Danh sách ảnh còn lại:', uploadedimages);
                            } else {
                                console.warn('Không tìm thấy ảnh trong danh sách:', photo);
                                console.log('Danh sách ảnh hiện tại:', uploadedimages);
                            }
                        }
                    });
                });
            }, 100);

            // Xử lý Dropzone
            const imageDropzone = new Dropzone("#editImageDropzone", {
                url: "{{ route('front.upload.avatar') }}",
                paramName: "photo",
                maxFilesize: 2,
                acceptedFiles: 'image/*',
                addRemoveLinks: true,
                dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
                dictRemoveFile: "Xóa ảnh",
                thumbnailWidth: 150,
                thumbnailHeight: 150,
                maxFiles: 5,
                headers: {
                    'X-CSRF-TOKEN': data.csrf_token
                },
                success: function(file, response) {
                    uploadedimages.push(response.link);
                    document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
                },
                removedfile: function(file) {
                    try {
                        if (file.xhr && file.xhr.response) {
                            const response = JSON.parse(file.xhr.response);
                            const index = uploadedimages.indexOf(response.link);
                            if (index !== -1) {
                                uploadedimages.splice(index, 1);
                                document.getElementById('uploadedimages').value = JSON.stringify(
                                    uploadedimages);
                            }
                        }
                    } catch (e) {
                        console.error("Lỗi khi xóa file:", e);
                    }
                    file.previewElement.remove();
                }
            });

            // Lưu instance vào biến toàn cục
            globalDropzones['editDropzone'] = imageDropzone;

            // Khởi tạo Tom Select cho tags
            var tomSelectEdit = new TomSelect('#edit-tags', {
                maxItems: 5,
                plugins: ['remove_button'],
                placeholder: 'Chọn hoặc tạo thẻ mới...',
                create: true,
                createFilter: function(input) {
                    return input.length >= 2;
                }
            });

            // Khởi tạo CKEditor
            if (typeof ClassicEditor !== 'undefined') {
                ClassicEditor.create(document.getElementById('edit-content'), {
                    ckfinder: {
                        uploadUrl: '{{ route('upload.ckeditor') . '?_token=' . csrf_token() }}'
                    }
                }).catch(error => {
                    console.error('CKEditor error:', error);
                });
            }

            // Thêm biến để theo dõi URLs đã bị xóa
            let deletedUrls = [];

            // Xử lý xóa URL
            document.querySelectorAll('.remove-url').forEach(button => {
                button.addEventListener('click', function() {
                    const urlField = this.closest('.url-field');
                    const urlInput = urlField.querySelector('input[name="urls[]"]');
                    const url = urlInput.value;

                    if (url && url.trim() !== '') {
                        // Thêm URL vào danh sách đã xóa
                        deletedUrls.push(url);
                        console.log('Đã đánh dấu URL để xóa:', url);
                        console.log('Danh sách URLs đã xóa:', deletedUrls);
                    }

                    // Xóa trường URL khỏi DOM
                    urlField.remove();
                });
            });

            // Xử lý thêm trường URL
            const addUrlButton = document.getElementById('add-url-field');
            const urlFields = document.getElementById('url-fields');

            if (addUrlButton) {
                addUrlButton.addEventListener('click', function() {
                    const urlField = document.createElement('div');
                    urlField.className = 'flex items-center mb-2 url-field';
                    urlField.innerHTML = `
                    <input type="text" name="urls[]" class="flex-grow px-3 py-2 border border-gray-300 rounded-md" placeholder="URL file (nếu có)">
                    <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-url">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                    urlFields.appendChild(urlField);

                    // Thêm sự kiện cho nút xóa mới
                    const removeButton = urlField.querySelector('.remove-url');
                    removeButton.addEventListener('click', function() {
                        const input = urlField.querySelector('input[name="urls[]"]');
                        const url = input.value;

                        if (url && url.trim() !== '') {
                            deletedUrls.push(url);
                            console.log('Đã đánh dấu URL mới để xóa:', url);
                        }

                        urlField.remove();
                    });
                });
            }

            // Xử lý xóa tài liệu
            document.querySelectorAll('.remove-document').forEach(button => {
                button.addEventListener('click', function() {
                    const docId = this.getAttribute('data-id');
                    const docElement = this.closest('.flex.items-center.justify-between');

                    if (confirm('Bạn có chắc chắn muốn xóa tài liệu này?')) {
                        // Đánh dấu trực quan tài liệu đã bị xóa
                        docElement.style.opacity = '0.5';
                        docElement.style.textDecoration = 'line-through';
                        this.disabled = true;
                        this.setAttribute('title', 'Đã đánh dấu xóa');

                        // Thêm input ẩn để đánh dấu tài liệu cần xóa
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'delete_documents[]';
                        hiddenInput.value = docId;
                        form.appendChild(hiddenInput);

                        // Hiển thị thông báo
                        const msgElement = document.createElement('span');
                        msgElement.className = 'text-xs text-red-500 ml-2';
                        msgElement.textContent = '(Sẽ bị xóa khi lưu)';
                        this.parentNode.insertBefore(msgElement, this);
                    }
                });
            });

            // Sửa lại submit form để gửi danh sách URLs đã xóa
            const form = document.getElementById('editPostForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);

                // Kiểm tra và thu thập ID của các tài liệu đã bị xóa
                const deletedDocIds = [];
                document.querySelectorAll('.remove-document[disabled]').forEach(btn => {
                    const docId = btn.getAttribute('data-id');
                    if (docId) {
                        deletedDocIds.push(docId);
                    }
                });

                // Thêm các ID đã xóa vào formData
                deletedDocIds.forEach(id => {
                    formData.append('delete_documents[]', id);
                });

                // Thêm các URLs đã xóa vào formData
                deletedUrls.forEach(url => {
                    formData.append('delete_urls[]', url);
                });

                // Hiển thị trạng thái loading
                const submitButton = form.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';

                // Thêm dữ liệu debug để theo dõi
                console.log('Dữ liệu gửi đi:', {
                    deletedDocIds,
                    deletedUrls,
                    urls: Array.from(formData.getAll('urls[]')),
                    documents: formData.getAll('delete_documents[]')
                });

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        // Phục hồi trạng thái nút submit
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;

                        if (response.redirected) {
                            window.location.href = response.url;
                            return {};
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.success) {
                            // Đóng modal và reload trang để thấy thay đổi
                            closeEditPostModal();
                            window.location.reload();
                        } else if (data && data.error) {
                            alert('Có lỗi xảy ra: ' + data.error);
                        } else {
                            closeEditPostModal();
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        // Phục hồi trạng thái nút submit nếu có lỗi
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;

                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi cập nhật bài viết');
                    });
            });
        }

        function openCreatePostModal() {
            const modal = document.getElementById('createPostModal');
            const content = document.getElementById('createPostContent');

            // Show modal with loading spinner
            modal.classList.remove('hidden');
            content.innerHTML =
                '<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div></div>';

            // Fetch form content from server
            fetch('{{ route('front.tblogs.get-form') }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 401) {
                            window.location.href = '{{ route('front.login') }}';
                            throw new Error('Bạn cần đăng nhập để thực hiện chức năng này.');
                        }
                        return response.json().then(data => {
                            throw new Error(data.error || 'Không thể tải form.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Render form
                    renderCreateForm(data, content);
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = `<div class="text-red-500 text-center p-4">${error.message}</div>`;
                });
        }

        function closeCreatePostModal() {
            const modal = document.getElementById('createPostModal');
            modal.classList.add('hidden');

            // Hủy bỏ instance Dropzone nếu có
            if (globalDropzones['createDropzone']) {
                try {
                    console.log('Đang hủy Dropzone khi đóng modal tạo bài viết...');
                    globalDropzones['createDropzone'].destroy();
                    console.log('Đã hủy Dropzone thành công');
                    delete globalDropzones['createDropzone'];
                } catch (error) {
                    console.error('Lỗi khi hủy Dropzone:', error);
                }
            }
        }

        function renderCreateForm(data, container) {
            // HTML form template
            const formHtml = `
            <form id="createPostForm" action="${data.store_url}" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="${data.csrf_token}">
                
                <!-- Upload ảnh đầu bài -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Upload hình ảnh</label>
                    <div class="dropzone border-2 border-dashed border-blue-400 rounded-lg p-4 bg-gray-50" id="createImageDropzone"></div>
                    <div id="createUploadStatus" class="mt-2 p-2 hidden"></div>
                </div>
                
                <!-- Ẩn input để lưu tên file ảnh -->
                <input type="hidden" name="photo" id="uploadedImages">
                
                <!-- Tiêu đề bài viết -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Tiêu đề</label>
                    <input type="text" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tiêu đề bài viết..." required>
                </div>

                <!-- Thẻ bài viết -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Thẻ bài viết</label>
                    <select id="tags" name="tags[]" multiple class="w-full">
                        ${data.tags.map(tag => `<option value="${tag.id}">${tag.title}</option>`).join('')}
                    </select>
                    <span class="text-sm text-gray-500">Tối đa 5 tag</span>
                    
                    <div class="flex flex-wrap gap-2 mt-2">
                        ${data.toptags.map(tag => 
                            `<button type="button" class="tag-button bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs hover:bg-blue-200" 
                                            data-tag-id="${tag.id}" data-tag-name="${tag.title}">
                                            #${tag.title}
                                        </button>`
                        ).join('')}
                    </div>
                </div>

                <!-- Nội dung bài viết -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Nội dung</label>
                    <textarea name="content" id="editor" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nội dung bài viết"></textarea>
                </div>

                <!-- Tài liệu đính kèm -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Tài liệu</label>
                    <input type="file" name="document[]" id="document" class="w-full px-3 py-2 border border-gray-300 rounded-md" multiple>
                    <input type="text" name="urls[]" class="w-full px-3 py-2 border border-gray-300 rounded-md mt-2" placeholder="URL file (nếu có)">
                </div>

                <!-- Trạng thái bài viết -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Trạng thái bài viết</label>
                    <div class="flex space-x-4">
                        <div class="flex items-center">
                            <input type="radio" id="status_public" name="status" value="1" class="mr-2" checked>
                            <label for="status_public" class="text-sm">
                                <i class="fas fa-globe-americas mr-1"></i> Công khai
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="status_private" name="status" value="0" class="mr-2">
                            <label for="status_private" class="text-sm">
                                <i class="fas fa-lock mr-1"></i> Chỉ mình tôi
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Nút hành động -->
                <div class="flex justify-between mt-6">
                    <button type="button" onclick="closeCreatePostModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100">Hủy</button>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Đăng bài</button>
                </div>
            </form>
        `;

            // Gán nội dung form vào container
            container.innerHTML = formHtml;

            // Kiểm tra và hủy instance Dropzone cũ nếu có
            if (globalDropzones['createDropzone']) {
                try {
                    console.log('Đang hủy Dropzone cũ...');
                    globalDropzones['createDropzone'].destroy();
                    console.log('Đã hủy Dropzone cũ thành công');
                } catch (error) {
                    console.error('Lỗi khi hủy Dropzone cũ:', error);
                }
                delete globalDropzones['createDropzone'];
            }

            // Khởi tạo các thành phần tương tác
            setTimeout(() => {
                initCreateFormComponents(data);
            }, 100);
        }

        // Thêm hàm khởi tạo các thành phần tương tác cho form tạo bài viết mới
        function initCreateFormComponents(data) {
            try {
                console.log('Đang khởi tạo các thành phần form tạo bài viết...');

                // Khởi tạo biến lưu danh sách ảnh
                const uploadedImages = [];
                const uploadStatus = document.getElementById('createUploadStatus');

                // Khởi tạo Dropzone cho upload ảnh
                const imageDropzone = new Dropzone("#createImageDropzone", {
                    url: "{{ route('front.upload.avatar') }}",
                    paramName: "photo",
                    maxFilesize: 2,
                    acceptedFiles: 'image/*',
                    addRemoveLinks: true,
                    dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
                    dictRemoveFile: "Xóa ảnh",
                    thumbnailWidth: 150,
                    thumbnailHeight: 150,
                    maxFiles: 5,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function(file, response) {
                        console.log('Tải lên thành công:', response);
                        uploadedImages.push(response.link);
                        document.getElementById('uploadedImages').value = JSON.stringify(uploadedImages);
                    },
                    removedfile: function(file) {
                        try {
                            if (file.xhr && file.xhr.response) {
                                const response = JSON.parse(file.xhr.response);
                                const index = uploadedImages.indexOf(response.link);
                                if (index !== -1) {
                                    uploadedImages.splice(index, 1);
                                    document.getElementById('uploadedImages').value = JSON.stringify(
                                        uploadedImages);
                                }
                            }
                        } catch (e) {
                            console.error("Lỗi khi xóa file:", e);
                        }
                        file.previewElement.remove();
                    }
                });

                // Lưu instance vào biến toàn cục
                globalDropzones['createDropzone'] = imageDropzone;
                console.log('Đã khởi tạo Dropzone thành công');

                // Khởi tạo Tom Select cho tags
                var tomSelectCreate = new TomSelect('#tags', {
                    maxItems: 5,
                    plugins: ['remove_button'],
                    placeholder: 'Chọn hoặc tạo thẻ mới...',
                    create: true,
                    createFilter: function(input) {
                        return input.length >= 2;
                    }
                });
                console.log('Đã khởi tạo TomSelect thành công');

                // Khởi tạo CKEditor
                ClassicEditor.create(document.querySelector('#editor'), {
                    ckfinder: {
                        uploadUrl: data.ckeditor_upload_url
                    },
                    mediaEmbed: {
                        previewsInData: true
                    }
                }).catch(error => {
                    console.error("Lỗi CKEditor:", error);
                });
                console.log('Đã khởi tạo CKEditor thành công');

                // Xử lý các nút tag
                document.querySelectorAll('.tag-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const tagId = this.dataset.tagId;
                        const tagName = this.dataset.tagName;

                        // Get Tom Select instance
                        if (tomSelectCreate) {
                            if (!tomSelectCreate.items.includes(tagId)) {
                                tomSelectCreate.addItem(tagId);
                            } else {
                                tomSelectCreate.removeItem(tagId);
                            }
                        }
                    });
                });
                console.log('Đã khởi tạo nút tag thành công');

                // Xử lý submit form
                document.getElementById('createPostForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (response.redirected) {
                                window.location.href = response.url;
                                return;
                            }

                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw new Error(data.error || 'Đã xảy ra lỗi khi đăng bài viết.');
                                });
                            }

                            return response.json();
                        })
                        .then(data => {
                            if (data && data.success) {
                                window.location.reload();
                            } else if (data) {
                                alert(data.message || 'Đã xảy ra lỗi khi đăng bài viết.');
                            } else {
                                window.location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert(error.message || 'Đã xảy ra lỗi khi đăng bài viết.');
                        });
                });
                console.log('Đã khởi tạo sự kiện submit form thành công');

            } catch (error) {
                console.error('Lỗi khởi tạo form:', error);
            }
        }

        // Toggle bookmark và cập nhật UI
        function toggleBookmark(postId, itemCode) {
            fetch('{{ route('front.tblog.bookmark') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
                    alert('Đã xảy ra lỗi khi lưu bài viết. Vui lòng thử lại sau.');
                });
        }
    </script>
@endsection
