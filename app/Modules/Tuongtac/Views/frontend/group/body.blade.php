@extends('frontend.layouts.master')
@section('css')
    {{-- <link rel="stylesheet" href="{{ asset('frontend/assets/css/custom8.css') }}" type="text/css" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('frontend/assets_f/custom-group.css') }}" type="text/css" /> --}}
    <!-- FilePond CSS -->
    <!-- Dropzone CSS -->
    @yield('topcss')
    <style>
        /* CSS cho cấu trúc 3 cột cố định */
        .blogs-container {
            display: flex;
            position: relative;
            width: 100%;
            min-height: calc(100vh - 60px);
            /* Điều chỉnh theo chiều cao thực tế của navbar */
            overflow: visible;
            /* Thay đổi từ hidden thành visible */
            background-color: #f9fafb;
        }

        /* Cột trái */
        .left-sidebar {
            width: 280px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: hidden;
            /* Ban đầu ẩn thanh cuộn */
            padding: 1rem 1rem 1rem 0;
            border-right: 1px solid #e5e7eb;
            scrollbar-width: thin;
            scrollbar-color: rgba(203, 213, 225, 0.6) transparent;
            transition: all 0.3s ease;
            background-color: white;
            z-index: 10;
        }

        .left-sidebar:hover {
            overflow-y: auto;
            /* Hiển thị thanh cuộn khi hover */
            box-shadow: 1px 0 5px rgba(0, 0, 0, 0.05);
        }

        /* Phần nội dung chính giữa */
        .main-content-wrapper {
            flex: 1;
            min-width: 0;
            /* Đảm bảo co lại khi màn hình nhỏ */
            max-width: 700px;
            margin: 0 auto;
            padding: 1rem 0;
            /* Bỏ các thuộc tính cuộn riêng */
            overflow: visible;
            position: relative;
            transition: all 0.3s ease;
        }

        /* Cột phải */
        .right-sidebar {
            width: 320px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: hidden;
            /* Ban đầu ẩn thanh cuộn */
            padding: 1rem 0rem 1rem 1rem !important;
            border-left: 1px solid #e5e7eb;
            scrollbar-width: thin;
            scrollbar-color: rgba(203, 213, 225, 0.6) transparent;
            transition: all 0.3s ease;
            background-color: white;
            z-index: 10;
        }

        .right-sidebar:hover {
            overflow-y: auto;
            /* Hiển thị thanh cuộn khi hover */
            box-shadow: -1px 0 5px rgba(0, 0, 0, 0.05);
        }

        /* Tùy chỉnh thanh cuộn cho hiệu ứng mượt mà */
        .left-sidebar::-webkit-scrollbar,
        .right-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .left-sidebar::-webkit-scrollbar-track,
        .right-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .left-sidebar::-webkit-scrollbar-thumb,
        .right-sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(203, 213, 225, 0.6);
            border-radius: 20px;
        }

        /* Hiệu ứng hover cho thanh cuộn */
        .left-sidebar:hover::-webkit-scrollbar-thumb,
        .right-sidebar:hover::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.8);
        }

        /* CSS cho dropdown menu */
        .dropdown-menu {
            display: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            transform: translateY(-10px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .dropdown-menu.active {
            display: block;
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Dropdown menu styles */
        .post-dropdown .dropdown-menu {
            transition: all 0.3s ease;
            transform: translateY(-10px);
            opacity: 0;
        }

        .post-dropdown .dropdown-menu.active,
        .post-dropdown .dropdown-menu:not(.hidden) {
            transform: translateY(0);
            opacity: 1;
            display: block;
        }

        /* Post action button styles */
        .post-action-btn {
            transition: all 0.3s ease;
        }

        .post-action-btn:hover {
            color: #3b82f6 !important;
            transform: translateY(-1px);
        }

        .post-action-btn.active {
            color: #3b82f6 !important;
        }

        /* Emoji picker styles */
        #emoji-picker {
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .emoji-btn {
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 50%;
        }

        .emoji-btn:hover {
            transform: scale(1.2);
            background-color: rgba(59, 130, 246, 0.1);
        }

        .post-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .mobile-menu {
            display: none;
            transition: all 0.3s ease;
        }

        .mobile-menu.active {
            display: flex;
            animation: slideDown 0.3s ease forwards;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sidebar.collapsed {
            width: 0;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        .main-content.expanded {
            width: 100%;
            max-width: 900px;
        }

        #main-content {
            max-width: 692.8px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .loading-spinner {
            display: none;
            transition: all 0.3s ease;
        }

        .loading-spinner.active {
            display: block;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .quick-view-modal {
            display: none;
            transition: all 0.3s ease;
        }

        .quick-view-modal.active {
            display: flex;
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .tag {
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .tag:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .comment-input {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .comment-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
            transform: translateY(-1px);
        }

        /* Popup Modal - Global */
        .popup-modal {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: all 0.3s ease;
            backdrop-filter: blur(3px);
        }

        .popup-modal.hidden {
            display: none;
        }

        .popup-content {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .popup-modal.active .popup-content {
            transform: scale(1);
            opacity: 1;
        }

        .close-popup {
            position: absolute;
            top: 16px;
            right: 16px;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #555;
            transition: all 0.3s ease;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-popup:hover {
            color: #222;
            background-color: rgba(0, 0, 0, 0.05);
        }

        /* Nút lên đầu trang */
        #scroll-to-top {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background-color: #3b82f6;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }

        #scroll-to-top:hover {
            transform: translateY(-3px);
            background-color: #2563eb;
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }

        #scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 1200px) {
            .left-sidebar {
                width: 240px;
            }

            .right-sidebar {
                width: 280px;
            }
        }

        @media (max-width: 992px) {
            .blogs-container {
                flex-direction: column;
            }

            .left-sidebar,
            .right-sidebar {
                width: 100%;
                max-width: 100%;
                height: auto;
                position: relative;
                border: none;
                overflow: visible;
                max-height: 300px;
                transition: max-height 0.3s ease;
            }

            .left-sidebar {
                border-bottom: 1px solid #e5e7eb;
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }

            .right-sidebar {
                border-top: 1px solid #e5e7eb;
                padding-top: 1rem;
                margin-top: 1rem;
            }

            .main-content-wrapper {
                max-width: 100%;
            }
        }

        @media (min-width: 769px) {
            .right-sidebar-mobile {
                display: none;
            }
        }

        .emoji-picker {
            display: none;
            position: absolute;
            bottom: 100%;
            right: 0;
            z-index: 10;
            transition: all 0.3s ease;
            transform: translateY(10px);
            opacity: 0;
        }

        .emoji-picker.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }
    </style>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
    {{-- @include('frontend.layouts.page_title') --}}
    <!-- Group Banner Section -->
    <section class="group-banner bg-gradient-to-r from-blue-500 to-blue-600 relative">
        <div class="absolute inset-0 bg-black bg-opacity-30"></div>

        @if ($group->cover_photo)
            <div class="absolute inset-0 overflow-hidden">
                <img src="{{ asset($group->cover_photo) }}" class="w-full h-full object-cover" alt="Banner nhóm">
            </div>
        @endif

        <!-- Banner Edit Button -->
        @if (Auth::check() && Auth::id() == $group->author_id)
            <button id="edit-cover-btn" class="group-banner-edit-btn">
                <i class="fas fa-pencil-alt"></i>
            </button>
        @endif

        <div class="container mx-auto px-4 relative z-10 h-full flex items-end pb-8">
            <div class="flex flex-col md:flex-row items-start md:items-end w-full">
                <div class="flex items-end">
                    <div class="group-avatar-container">
                        <img src="{{ $group->photo ? asset($group->photo) : asset('images/lego-head.png') }}"
                            alt="{{ $group->title }}" class="group-avatar">
                        @if (Auth::check() && Auth::id() == $group->author_id)
                            <button id="edit-photo-btn" class="group-avatar-edit-btn">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        @endif
                    </div>
                    <div class="group-info mb-4">
                        <!-- Thêm thông tin loại nhóm vào phần thông tin nhóm trên banner -->
                        <div class="flex items-center">
                            <h1 class="text-3xl font-bold text-white">{{ $group->title }}</h1>

                            <span
                                style="margin-left: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; color: white; background-color: {{ $group->is_private ? '#dc3545' : '#28a745' }};">
                                {{ $group->is_private ? 'Riêng tư' : 'Công khai' }}
                            </span>

                            @if (isset($group->groupType))
                                <span
                                    style="margin-left: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; color: white; background-color: #17a2b8;">
                                    <i class="fas fa-tag mr-1"></i> {{ $group->groupType->title }}
                                </span>
                            @endif
                        </div>
                        <p class="text-white mt-2">{{ $group->description }}</p>
                        <div class="flex items-center text-white text-sm mt-2">
                            @if ($isMember || !$group->is_private)
                                <span class="flex items-center mr-4">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ count(json_decode($group->members ?? '[]', true)) }}
                                    thành viên
                                </span>
                                <span class="flex items-center mr-4">
                                    <i class="fas fa-newspaper mr-1"></i>
                                    {{ isset($posts) ? (method_exists($posts, 'total') ? $posts->total() : count($posts)) : 0 }}
                                    bài viết
                                </span>
                            @else
                                <span class="flex items-center mr-4">
                                    <i class="fas fa-lock mr-1"></i> Nhóm riêng tư
                                </span>
                            @endif
                            <span class="flex items-center">
                                <i class="fas fa-calendar-alt mr-1"></i> Thành lập
                                {{ date('d/m/Y', strtotime($group->created_at)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Group Actions (Desktop) -->
                <div class="group-actions-desktop ml-auto space-x-3 mb-4">
                    @if (Auth::check())
                        @if ($isMember)
                            <a href="javascript:void(0);" onclick="openCreatePostModal()"
                                class="bg-white text-blue-600 px-4 py-2 rounded-md flex items-center font-medium hover:bg-gray-100 whitespace-nowrap">
                                <i class="fas fa-plus mr-2"></i> Đăng bài
                            </a>
                            @if (Auth::id() == $group->author_id)
                                <form action="{{ route('group.destroy', $group->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn giải tán nhóm này không? Hành động này không thể hoàn tác.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 text-white px-4 py-2 rounded-md flex items-center font-medium hover:bg-red-600 whitespace-nowrap">
                                        <i class="fas fa-trash-alt mr-2"></i> Giải tán nhóm
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('group.leave', $group->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="bg-red-500 text-white px-4 py-2 rounded-md flex items-center font-medium hover:bg-red-600 whitespace-nowrap">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Rời nhóm
                                    </button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('group.join', $group->id) }}" method="POST" class="inline">
                                @csrf
                                @if (isset($joinRequest) && $joinRequest && $joinRequest->status == 'pending')
                                    <button type="button"
                                        class="bg-yellow-500 text-white px-4 py-2 rounded-md flex items-center font-medium whitespace-nowrap"
                                        disabled>
                                        <i class="fas fa-clock mr-2"></i> Đang chờ duyệt
                                    </button>
                                @else
                                    <button type="submit"
                                        class="bg-white text-blue-600 px-4 py-2 rounded-md flex items-center font-medium hover:bg-gray-100 whitespace-nowrap">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        {{ $group->is_private ? 'Yêu cầu tham gia' : 'Tham gia nhóm' }}
                                    </button>
                                @endif
                            </form>
                        @endif
                        @if (Auth::id() == $group->author_id)
                            <a href="{{ route('group.edit', $group->id) }}"
                                class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md flex items-center font-medium hover:bg-gray-300 whitespace-nowrap">
                                <i class="fas fa-cog mr-2"></i> Chỉnh sửa
                            </a>
                        @endif
                    @else
                        <a href="{{ route('front.login') }}"
                            class="bg-white text-blue-600 px-4 py-2 rounded-md flex items-center font-medium hover:bg-gray-100 whitespace-nowrap">
                            <i class="fas fa-sign-in-alt mr-2"></i> Đăng nhập để tham gia
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </section>

    <!-- Group Actions (Mobile) -->
    <div class="group-actions-mobile bg-white shadow-sm py-2 px-4">
        <div class="flex justify-between">
            @if (Auth::check() && $isMember)
                <a href="javascript:void(0);" onclick="openCreatePostModal()"
                    class="text-blue-600 flex flex-col items-center text-xs">
                    <i class="fas fa-plus text-lg mb-1"></i>
                    <span>Đăng bài</span>
                </a>
                @if (Auth::id() == $group->author_id)
                    <form action="{{ route('group.destroy', $group->id) }}" method="POST"
                        onsubmit="return confirm('Bạn có chắc chắn muốn giải tán nhóm này không? Hành động này không thể hoàn tác.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 flex flex-col items-center text-xs">
                            <i class="fas fa-trash-alt text-lg mb-1"></i>
                            <span>Giải tán</span>
                        </button>
                    </form>
                @else
                    <form action="{{ route('group.leave', $group->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-red-500 flex flex-col items-center text-xs">
                            <i class="fas fa-sign-out-alt text-lg mb-1"></i>
                            <span>Rời nhóm</span>
                        </button>
                    </form>
                @endif
                @if (Auth::id() == $group->author_id)
                    <a href="{{ route('group.edit', $group->id) }}"
                        class="text-gray-600 flex flex-col items-center text-xs">
                        <i class="fas fa-cog text-lg mb-1"></i>
                        <span>Chỉnh sửa</span>
                    </a>
                @endif
            @elseif(Auth::check())
                <form action="{{ route('group.join', $group->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-blue-600 flex flex-col items-center text-xs">
                        <i class="fas fa-user-plus text-lg mb-1"></i>
                        <span>{{ $group->is_private ? 'Yêu cầu tham gia' : 'Tham gia' }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('front.login') }}" class="text-blue-600 flex flex-col items-center text-xs">
                    <i class="fas fa-sign-in-alt text-lg mb-1"></i>
                    <span>Đăng nhập</span>
                </a>
            @endif
            <button id="mobile-group-menu" class="text-gray-600 flex flex-col items-center text-xs">
                <i class="fas fa-ellipsis-h text-lg mb-1"></i>
                <span>Thêm</span>
            </button>
        </div>
    </div>

    <!-- Mobile Group Menu Dropdown -->
    <div id="mobile-group-dropdown" class="mobile-menu hidden bg-white shadow-md">
        <a href="#" class="block px-4 py-3 border-b border-gray-100 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-info-circle mr-2"></i> Giới thiệu nhóm
        </a>
        <a href="#" class="block px-4 py-3 border-b border-gray-100 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-users mr-2"></i> Thành viên
        </a>
        <a href="#" class="block px-4 py-3 border-b border-gray-100 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-star mr-2"></i> Bài viết nổi bật
        </a>
        <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-tags mr-2"></i> Chủ đề
        </a>
    </div>

    <div class="blogs-container">
        <!-- Left Menu -->
        <div class="left-sidebar">
            @include('Tuongtac::frontend.group.partials.left-sidebar')
        </div>

        <!-- Main Content -->
        <div class="main-content-wrapper">
            @yield('inner-content')
        </div>

        <!-- Right Menu -->
        <div class="right-sidebar">
            @include('Tuongtac::frontend.group.partials.right-sidebar')
        </div>
    </div>

    <!-- Nút cuộn lên đầu trang -->
    <div id="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        var csrfToken = '{{ csrf_token() }}';

        // Xử lý hiệu ứng nút cuộn lên đầu trang
        document.addEventListener('DOMContentLoaded', function() {
            const scrollTopBtn = document.getElementById('scroll-to-top');

            // Hiển thị nút khi cuộn xuống
            function toggleScrollButton() {
                if (document.documentElement.scrollTop > 300) {
                    scrollTopBtn.classList.add('show');
                } else {
                    scrollTopBtn.classList.remove('show');
                }
            }

            // Xử lý sự kiện khi cuộn trang
            window.addEventListener('scroll', toggleScrollButton);

            // Xử lý sự kiện khi nhấp vào nút
            scrollTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>

  

    <!-- Additional Scripts -->
    @yield('botscript')
@endsection
