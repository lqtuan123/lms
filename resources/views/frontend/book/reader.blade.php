@extends('frontend.layouts.master1')

@php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
@endphp

@section('title', $book->title . ' - Đọc Sách')

@section('css')
<link rel="stylesheet" href="{{ asset('frontend/css/book/reader.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Các lớp CSS cho chế độ tối */
    .dark-mode-content {
        background-color: #121212;
        color: #e0e0e0;
    }
    
    .dark-mode-content .container-fluid {
        background-color: #121212;
    }
    
    .dark-mode-content .reader-container {
        background-color: #1e1e1e;
    }
    
    .dark-mode-content .header-container {
        background-color: #2d2d2d;
        color: #ffffff;
    }
    
    .dark-mode-content .book-title {
        color: #f0f0f0;
    }
    
    .dark-mode-content .pdf-controls {
        background-color: #2d2d2d;
        border-color: #444;
    }
    
    .dark-mode-content .pdf-button,
    .dark-mode-content .action-button {
        background-color: #3d3d3d;
        color: #e0e0e0;
        border-color: #555;
    }
    
    .dark-mode-content .pdf-pagination-info {
        color: #cccccc;
    }
    
    .dark-mode-content .settings-dropdown {
        background-color: #2d2d2d;
        border-color: #444;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    }
    
    .dark-mode-content .settings-item {
        border-color: #444;
    }
    
    .dark-mode-content .zoom-controls {
        background-color: #3d3d3d;
    }
    
    .dark-mode-content .zoom-button {
        background-color: #4d4d4d;
        color: #e0e0e0;
    }
    
    .dark-mode-content .zoom-info {
        color: #cccccc;
    }
    
    .dark-mode-content .dark-mode-label {
        color: #e0e0e0;
    }
    
    .dark-mode-content .toggle {
        background-color: #3d3d3d;
    }
    
    .dark-mode-content .stats-button {
        background-color: #3d3d3d;
        color: #e0e0e0;
    }
    
    .dark-mode-content .reading-stats {
        background-color: #2d2d2d;
        color: #e0e0e0;
        border-color: #444;
    }
    
    .dark-mode-content .reading-stats-title {
        background-color: #3d3d3d;
        color: #f0f0f0;
    }
    
    .dark-mode-content .alert {
        background-color: #2d2d2d;
        color: #f0f0f0;
        border-color: #444;
    }
    
    .dark-mode-content .alert-warning {
        background-color: #332b00;
        border-color: #665500;
        color: #ffd54f;
    }
    
    .dark-mode-content #pdf-content {
        background-color: #1e1e1e;
    }
    
    .dark-mode-content #reader-status {
        background-color: #2d2d2d;
        color: #e0e0e0;
    }

    /* CSS cho thanh header ẩn/hiện */
    header.hidden-header {
        transform: translateY(-100%);
        position: fixed;
        width: 100%;
        z-index: 40;
    }
    
    header {
        transition: transform 0.3s ease;
        position: fixed;
        width: 100%;
        z-index: 40;
    }

    /* Đảm bảo nội dung không bị che khuất khi header ẩn */
    main {
        padding-top: 70px; /* Chiều cao của header */
    }

    /* CSS cho thanh điều khiển PDF nổi cố định ở đáy */
    .pdf-controls {
        bottom: 0;
        left: 0;
        right: 0;
        height: 42px; /* Cố định chiều cao 42px */
        background-color: #fff;
        padding: 0 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transition: transform 0.3s ease, opacity 0.3s ease;
        opacity: 1;
        transform: translateY(0);
        border-top: 1px solid #e5e5e5;
        margin: 0;
    }
    
    .pdf-controls.hidden {
        transform: translateY(100%);
        opacity: 0;
    }
    
    /* Đảm bảo không gian bên dưới PDF để tránh bị che nội dung */
    #pdf-content {
        margin-bottom: 50px; /* Chiều cao của thanh điều khiển + padding */
    }

    /* Điều chỉnh style cho các nút trong thanh điều khiển */
    .pdf-button, .action-button {
        border: 1px solid #ddd;
        background-color: #f8f9fa;
        border-radius: 4px;
        padding: 5px 10px;
        margin: 0 5px;
        font-size: 13px;
        height: 30px;
        line-height: 1;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
    }

    .pdf-button:hover, .action-button:hover {
        background-color: #e9ecef;
    }

    .pdf-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Style cho thông tin trang */
    .pdf-pagination-info {
        margin: 0 15px;
        font-size: 14px;
    }

    /* Điều chỉnh dropdown settings */
    .settings-dropdown {
        position: absolute;
        bottom: 45px;
        right: 0;
        width: 250px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        display: none;
    }

    /* Thêm responsive */
    @media (max-width: 768px) {
        .pdf-controls {
            padding: 0 10px;
        }
        
        .pdf-button, .action-button {
            padding: 4px 6px;
            font-size: 12px;
            margin: 0 3px;
        }
        
        .pdf-pagination-info {
            margin: 0 8px;
            font-size: 12px;
        }
    }

    /* Đảm bảo thanh điều khiển không bị lỗi trên thiết bị nhỏ */
    @media (max-width: 576px) {
        .pdf-button, .action-button {
            padding: 4px;
            margin: 0 2px;
        }
        
        .pdf-pagination-info {
            margin: 0 4px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid reader-container py-4 px-0" data-user-logged-in="{{ Auth::check() ? '1' : '0' }}" data-book-id="{{ $book->id }}">
    <input type="hidden" name="book_id" value="{{ $book->id }}">
    
    <div>
        <div class="d-flex justify-content-center p-2">
            <div class="book-header text-center">
                <h1 class="book-title mb-0">{{ $book->title }}</h1>
            </div>
        </div>
    </div>
    
    <div id="loading-indicator" class="loading-indicator">
        <div class="loading-spinner"></div>
        <p>Đang tải sách...</p>
    </div>
    
    @if($resources->count() > 0)
        <?php $pdfFound = false; ?>
        <div id="pdf-content" class="pdf-content"></div>
        
        <script>
            // Danh sách tất cả tài nguyên PDF để hiển thị
            const pdfResources = [
                @foreach($resources as $resource)
                    @if(Str::endsWith($resource->url, '.pdf') || $resource->file_type == 'application/pdf')
                        '{{ asset($resource->url) }}',
                        <?php $pdfFound = true; ?>
                    @endif
                @endforeach
            ];
            
            // Biến để lưu URL PDF hiện tại đang hiển thị
            let currentPdfUrl = '';
            
            // Nếu có tài nguyên PDF, lưu URL đầu tiên
            if (pdfResources.length > 0) {
                currentPdfUrl = pdfResources[0];
            }
        </script>
        
        @if(!$pdfFound)
            <div class="alert alert-warning">
                Không tìm thấy tài liệu PDF cho sách này.
            </div>
        @endif
    @else
        <div class="alert alert-warning">
            Không tìm thấy tài liệu cho sách này.
        </div>
    @endif
    
    <!-- Thống kê thời gian đọc -->
    <div id="reading-stats" class="reading-stats @if(!Auth::check()) d-none @endif">
        <div class="reading-stats-title">
            Tiến độ
            <span class="reading-stats-close" onclick="toggleReadingStats()"></span>
        </div>
        <div class="reading-stats-item">
            <span>Đọc:</span>
            <span id="reading-time">0</span> phút
        </div>
        <div class="reading-stats-item">
            <span>Điểm:</span>
            <span id="points-earned">0</span>
        </div>
    </div>

    <!-- Thông báo trạng thái đọc sách -->
    <div id="reader-status" class="reader-status"></div>
    
    <!-- Thanh điều khiển PDF nổi ở đáy -->
    <div id="pdf-controls" class="pdf-controls">
        <button id="prev-page" class="pdf-button" disabled>
            <i class="fas fa-chevron-left mr-1"></i> Trang trước
        </button>
        
        <span id="page-info" class="pdf-pagination-info">
            Trang <span id="current-page">-</span> / <span id="total-pages">-</span>
        </span>
        
        <button id="next-page" class="pdf-button" disabled>
            Trang sau <i class="fas fa-chevron-right ml-1"></i>
        </button>
        
        <button id="bookmark-btn" class="action-button">
            <i class="far fa-heart"></i>
        </button>
        
        <div class="position-relative d-inline-block">
            <button id="settings-btn" class="action-button settings">
                <i class="fas fa-cog"></i>
            </button>
            
            <div id="settings-content" class="settings-dropdown">
                <div class="settings-item">
                    <div class="zoom-controls">
                        <button id="zoom-out" class="zoom-button" disabled>-</button>
                        <span id="zoom-info" class="zoom-info">75%</span>
                        <button id="zoom-in" class="zoom-button" disabled>+</button>
                    </div>
                </div>
                <div class="settings-item">
                    <label class="dark-mode-switch">
                        <span class="dark-mode-label">Chế độ tối</span>
                        <input type="checkbox" id="dark-mode-toggle" style="display: none;">
                        <div class="toggle">
                            <div class="toggle-handle"></div>
                        </div>
                    </label>
                </div>
                <div class="settings-item">
                    <button id="stats-toggle" class="stats-button">Hiện thống kê</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script src="{{ asset('frontend/js/book/pdf_reader.js') }}"></script>
<script>
    // Script xử lý chế độ tối và ẩn/hiện thanh điều khiển
    document.addEventListener('DOMContentLoaded', function() {
        const mainElement = document.querySelector('main.py-4');
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const headerElement = document.querySelector('header');
        
        // Kiểm tra trạng thái chế độ tối từ localStorage
        const isDarkMode = localStorage.getItem('reader_dark_mode') === 'true';
        
        // Áp dụng chế độ tối nếu đã được bật trước đó
        if (isDarkMode && mainElement) {
            mainElement.classList.add('dark-mode-content');
            if (darkModeToggle) {
                darkModeToggle.checked = true;
            }
        }
        
        // Xử lý sự kiện khi toggle chế độ tối
        if (darkModeToggle) {
            darkModeToggle.addEventListener('change', function() {
                if (mainElement) {
                    if (this.checked) {
                        // Bật chế độ tối
                        mainElement.classList.add('dark-mode-content');
                        localStorage.setItem('reader_dark_mode', 'true');
                    } else {
                        // Tắt chế độ tối
                        mainElement.classList.remove('dark-mode-content');
                        localStorage.setItem('reader_dark_mode', 'false');
                    }
                }
            });
        }
        
        // Xử lý hiển thị/ẩn thanh điều khiển PDF và header khi cuộn
        const pdfControls = document.getElementById('pdf-controls');
        let lastScrollY = window.scrollY;
        let scrollTimeout = null;
        let isControlsVisible = true;
        let isHeaderVisible = true;
        
        // Hàm ẩn thanh điều khiển
        function hideControls() {
            if (isControlsVisible) {
                pdfControls.classList.add('hidden');
                isControlsVisible = false;
            }
        }
        
        // Hàm hiện thanh điều khiển
        function showControls() {
            if (!isControlsVisible) {
                pdfControls.classList.remove('hidden');
                isControlsVisible = true;
            }
        }
        
        // Hàm ẩn header
        function hideHeader() {
            if (isHeaderVisible && headerElement) {
                headerElement.classList.add('hidden-header');
                isHeaderVisible = false;
            }
        }
        
        // Hàm hiện header
        function showHeader() {
            if (!isHeaderVisible && headerElement) {
                headerElement.classList.remove('hidden-header');
                isHeaderVisible = true;
            }
        }
        
        // Xử lý sự kiện cuộn trang
        function handleScroll() {
            const currentScrollY = window.scrollY;
            
            // Hiện thanh điều khiển và header khi người dùng cuộn lên trên
            if (currentScrollY < lastScrollY) {
                showControls();
                showHeader();
            } 
            // Ẩn thanh điều khiển và header khi người dùng cuộn xuống dưới
            else if (currentScrollY > lastScrollY && currentScrollY > 100) {
                hideControls();
                hideHeader();
            }
            
            lastScrollY = currentScrollY;
            
            // Thiết lập timeout để ẩn thanh điều khiển và header sau khi người dùng không tương tác
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (window.scrollY > 100) {
                    hideControls();
                    hideHeader();
                }
            }, 3000); // Ẩn sau 3 giây không tương tác
        }
        
        // Đăng ký sự kiện cuộn trang
        window.addEventListener('scroll', handleScroll, { passive: true });
        
        // Hiện thanh điều khiển khi người dùng di chuột
        document.addEventListener('mousemove', function(e) {
            // Chỉ hiện thanh khi chuột ở gần đáy màn hình
            const windowHeight = window.innerHeight;
            if (e.clientY > windowHeight - 100) {
                showControls();
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    if (window.scrollY > 100) {
                        hideControls();
                    }
                }, 3000);
            }
            
            // Hiện header khi chuột ở gần đỉnh màn hình
            if (e.clientY < 50) {
                showHeader();
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    if (window.scrollY > 100) {
                        hideHeader();
                    }
                }, 3000);
            }
        });
        
        // Xử lý cho thiết bị cảm ứng
        let touchStartY = 0;
        
        document.addEventListener('touchstart', function(e) {
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        document.addEventListener('touchmove', function(e) {
            const touchY = e.touches[0].clientY;
            const diff = touchY - touchStartY;
            
            // Hiện thanh điều khiển và header khi người dùng vuốt lên
            if (diff > 30) {
                showControls();
                showHeader();
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    hideControls();
                    hideHeader();
                }, 3000);
            } 
            // Ẩn thanh điều khiển và header khi người dùng vuốt xuống
            else if (diff < -30) {
                hideControls();
                hideHeader();
            }
            
            touchStartY = touchY;
        }, { passive: true });
        
        // Hiện thanh điều khiển khi người dùng chạm vào đáy màn hình
        document.addEventListener('touchend', function(e) {
            const touchY = e.changedTouches[0].clientY;
            const windowHeight = window.innerHeight;
            
            if (touchY > windowHeight - 100) {
                showControls();
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    hideControls();
                }, 3000);
            }
            
            // Hiện header khi người dùng chạm vào đỉnh màn hình
            if (touchY < 50) {
                showHeader();
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    hideHeader();
                }, 3000);
            }
        }, { passive: true });
    });
</script>
@endsection 