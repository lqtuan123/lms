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
            transition: overflow-y 0.3s ease;
        }

        .left-sidebar:hover {
            overflow-y: auto;
            /* Hiển thị thanh cuộn khi hover */
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
            transition: overflow-y 0.3s ease;
        }

        .right-sidebar:hover {
            overflow-y: auto;
            /* Hiển thị thanh cuộn khi hover */
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
            transition: all 0.2s ease;
        }

        .dropdown-menu.active {
            display: block;
            opacity: 1;
            visibility: visible;
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
            transition: all 0.2s ease;
        }

        .post-action-btn:hover {
            color: #3b82f6 !important;
        }

        .post-action-btn.active {
            color: #3b82f6 !important;
        }

        /* Emoji picker styles */
        #emoji-picker {
            transition: all 0.2s ease;
        }

        .emoji-btn {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .emoji-btn:hover {
            transform: scale(1.2);
        }

        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .mobile-menu {
            display: none;
        }

        .mobile-menu.active {
            display: flex;
        }

        .sidebar {
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 0;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        .main-content.expanded {
            width: 100%;
        }

        #main-content {
            max-width: 692.8px;
            width: 100%;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.active {
            display: block;
        }

        .quick-view-modal {
            display: none;
        }

        .quick-view-modal.active {
            display: flex;
        }

        .tag:hover {
            transform: scale(1.05);
        }

        .comment-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
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
        }

        .emoji-picker.active {
            display: block;
        }

        /* aaaaaaaaaaaaaaa */
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
            transition: color 0.2s;
        }

        .close-popup:hover {
            color: #222;
        }

        /* Post Dropdown */
        .post-dropdown {
            position: relative;
        }

        .post-dropdown .dropdown-toggle {
            background: white;
            border-radius: 9999px;
            padding: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .post-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 8px;
            width: 192px;
            /* 48 * 4 px */
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            z-index: 50;
            overflow: hidden;
        }

        .post-dropdown:hover .dropdown-menu,
        .post-dropdown .dropdown-toggle:focus+.dropdown-menu {
            display: block;
        }

        .dropdown-menu a,
        .dropdown-menu button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 10px 16px;
            font-size: 14px;
            color: #333;
            background: none;
            border: none;
            cursor: pointer;
        }

        .dropdown-menu a:hover,
        .dropdown-menu button:hover {
            background-color: #f7fafc;
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
        }

        #scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }
    </style>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
    {{-- @include('frontend.layouts.page_title') --}}
    <!-- Profile Banner Section -->
    <section class="profile-cover bg-gradient-to-r from-blue-500 to-blue-600 relative">

        @if ($profile->banner)
            <div class="absolute inset-0 overflow-hidden">
                <img src="{{ strpos($profile->banner, 'http') === 0 ? $profile->banner : asset($profile->banner) }}"
                    class="w-full h-full object-cover" alt="Banner profile">
            </div>
        @endif

        <!-- Banner Edit Button -->
        @if (isset($isOwner) && $isOwner)
            <div class="absolute top-4 right-4 z-20">
                <button id="edit-banner-btn"
                    class="bg-white bg-opacity-80 text-gray-800 p-2 rounded-full hover:bg-opacity-100 transition-all opacity-0 hover:opacity-100 focus:opacity-100">
                    <i class="fas fa-pencil-alt"></i>
                </button>
            </div>
        @endif

        <div class="container mx-auto px-4 relative z-10 h-full flex items-end pb-8">
            <div class="flex flex-col md:flex-row items-start md:items-end w-full">
                <div class="flex items-end relative">
                    <!-- Avatar and Edit Button -->
                    <div class="relative group">
                        <img src="{{ strpos($profile->photo, 'http') === 0 ? $profile->photo : ($profile->photo ? asset($profile->photo) : asset('backend/images/profile-6.jpg')) }}"
                            alt="Profile Avatar" class="profile-avatar w-32 h-32 rounded-full object-cover bg-white mr-4">
                        @if (isset($isOwner) && $isOwner)
                            <button id="edit-avatar-btn"
                                class="absolute inset-0 m-auto w-10 h-10 bg-white bg-opacity-80 text-gray-800 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        @endif
                    </div>
                    <div class="mb-4">
                        <div class="flex items-center">
                            <h1 class="text-3xl font-bold text-white">{{ $profile->full_name }}</h1>
                            <span class="ml-3 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                {{ $profile->status == 'active' ? 'Đang hoạt động' : 'Không hoạt động' }}
                            </span>
                        </div>
                        <p class="text-white mt-2">{{ $profile->description ?? 'Người dùng chưa cập nhật mô tả' }}</p>
                        <div class="flex items-center text-white text-sm mt-2">
                            <span class="flex items-center mr-4">
                                <i class="fas fa-calendar-alt mr-1"></i> Tham gia từ
                                {{ \Carbon\Carbon::parse($profile->created_at)->format('d/m/Y') }}
                            </span>
                            <span class="flex items-center mr-4">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $profile->address ?? 'Chưa cập nhật địa chỉ' }}
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-user-friends mr-1"></i> {{ $followersCount ?? 0 }} người theo dõi
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Profile Actions (Desktop) -->
                <div class="profile-actions-desktop ml-auto space-x-3 mb-4">
                    @if (isset($isOwner) && $isOwner)
                        <button type="button" id="profile-edit-btn"
                            class="bg-white text-blue-600 px-4 py-2 rounded-md flex items-center font-medium hover:bg-gray-100">
                            <i class="fas fa-pencil-alt mr-2"></i> Chỉnh sửa hồ sơ
                        </button>
                        <button
                            class="bg-blue-500 text-white px-4 py-2 rounded-md flex items-center font-medium hover:bg-blue-600">
                            <i class="fas fa-share-alt mr-2"></i> Chia sẻ
                        </button>
                        <button id="settings-btn"
                            class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md flex items-center font-medium hover:bg-gray-300">
                            <i class="fas fa-cog mr-2"></i> Cài đặt
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </section>


    <!-- Profile Actions (Mobile) -->
    <div class="profile-actions-mobile bg-white shadow-sm py-2 px-4">
        <div class="flex justify-between">
            @if (isset($isOwner) && $isOwner)
                <a href="{{ route('front.profile') }}" class="text-blue-600 flex flex-col items-center text-xs">
                    <i class="fas fa-pencil-alt text-lg mb-1"></i>
                    <span>Chỉnh sửa</span>
                </a>
                <button class="text-blue-500 flex flex-col items-center text-xs">
                    <i class="fas fa-share-alt text-lg mb-1"></i>
                    <span>Chia sẻ</span>
                </button>
                <button id="settings-btn-mobile" class="text-gray-600 flex flex-col items-center text-xs">
                    <i class="fas fa-cog text-lg mb-1"></i>
                    <span>Cài đặt</span>
                </button>
                <button id="mobile-profile-menu" class="text-gray-600 flex flex-col items-center text-xs">
                    <i class="fas fa-ellipsis-h text-lg mb-1"></i>
                    <span>Thêm</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Mobile Profile Menu Dropdown -->
    <div id="mobile-profile-dropdown" class="mobile-menu hidden bg-white shadow-md">
        <a href="#personal-info" class="tab-link block px-4 py-3 border-b border-gray-100 text-gray-700 hover:bg-gray-50"
            data-tab="personal-info">
            <i class="fas fa-user mr-2"></i> Thông tin cá nhân
        </a>
        <a href="#posts" class="tab-link block px-4 py-3 border-b border-gray-100 text-gray-700 hover:bg-gray-50"
            data-tab="posts">
            <i class="fas fa-newspaper mr-2"></i> Bài viết đã đăng
        </a>
        <a href="#books" class="tab-link block px-4 py-3 border-b border-gray-100 text-gray-700 hover:bg-gray-50"
            data-tab="books">
            <i class="fas fa-book mr-2"></i> Sách đã đăng
        </a>
        <a href="#likes" class="tab-link block px-4 py-3 border-b border-gray-100 text-gray-700 hover:bg-gray-50"
            data-tab="likes">
            <i class="fas fa-heart mr-2"></i> Đã thích
        </a>
    </div>

    <div class="blogs-container">
        <!-- Left Menu -->
        <div class="left-sidebar">
            @include('frontend.profile.leftsidebar')
        </div>

        <!-- Main Content -->
        <div class="main-content-wrapper">
            @yield('inner-content')
        </div>

        <!-- Right Menu -->
        <div class="right-sidebar">
            @include('frontend.profile.rightsidebar')
        </div>
    </div>

    

  
@endsection
