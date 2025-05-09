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
            min-height: calc(100vh - 60px); /* Điều chỉnh theo chiều cao thực tế của navbar */
            overflow: visible; /* Thay đổi từ hidden thành visible */
        }

        /* Cột trái */
        .left-sidebar {
            width: 280px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: hidden; /* Ban đầu ẩn thanh cuộn */
            padding: 1rem 1rem 1rem 0;
            border-right: 1px solid #e5e7eb;
            scrollbar-width: thin;
            scrollbar-color: rgba(203, 213, 225, 0.6) transparent;
            transition: overflow-y 0.3s ease;
        }
        
        .left-sidebar:hover {
            overflow-y: auto; /* Hiển thị thanh cuộn khi hover */
        }

        /* Phần nội dung chính giữa */
        .main-content-wrapper {
            flex: 1;
            min-width: 0; /* Đảm bảo co lại khi màn hình nhỏ */
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
            overflow-y: hidden; /* Ban đầu ẩn thanh cuộn */
            padding: 1rem 0rem 1rem 1rem !important;
            border-left: 1px solid #e5e7eb;
            scrollbar-width: thin;
            scrollbar-color: rgba(203, 213, 225, 0.6) transparent;
            transition: overflow-y 0.3s ease;
        }
        
        .right-sidebar:hover {
            overflow-y: auto; /* Hiển thị thanh cuộn khi hover */
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
            
            .left-sidebar, .right-sidebar {
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
    
    <div class="blogs-container">
        <!-- Left Menu -->
        <div class="left-sidebar">
            @include('Tuongtac::frontend.blogs.left-partial')
        </div>

        <!-- Main Content -->
        <div class="main-content-wrapper">
            @yield('inner-content')
        </div>

        <!-- Right Menu -->
        <div class="right-sidebar">
            @include('Tuongtac::frontend.blogs.right-partial')
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

    <!-- Social Interactions JavaScript -->
    @socialInteractions

    <!-- Additional Scripts -->
    @yield('botscript')
@endsection

