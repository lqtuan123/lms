<?php

$detail = \App\Models\SettingDetail::find(1);
$user = auth()->user();

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    @include('frontend.layouts.head')
    <style>
        /* CSS toàn cục - Kích thước chung */
        html {
            font-size: 15px; /* Giảm font-size cơ bản từ 16px xuống 15px */
        }
        
        /* Container có kích thước nhất quán */
        .container {
            max-width: 95%;
            padding-left: 1rem;
            padding-right: 1rem;
            margin-left: auto;
            margin-right: auto;
        }
        
        @media (min-width: 1200px) {
            .container {
                max-width: 1140px;
            }
        }
        
        @media (min-width: 992px) and (max-width: 1199px) {
            .container {
                max-width: 960px;
            }
        }
        
        @media (min-width: 768px) and (max-width: 991px) {
            .container {
                max-width: 720px;
            }
        }
        
        @media (min-width: 576px) and (max-width: 767px) {
            .container {
                max-width: 540px;
            }
        }
        
        /* Kích thước cho các phần tử phổ biến */
        .card {
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }
        
        .form-control {
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            border-radius: 0.375rem;
        }
        
        .table td, .table th {
            padding: 0.5rem 0.75rem;
        }
        
        .nav-link {
            padding: 0.5rem 1rem;
        }
        
        /* Kích thước tiêu đề đồng nhất */
        h1, .h1 { font-size: 1.75rem; margin-bottom: 1rem; }
        h2, .h2 { font-size: 1.5rem; margin-bottom: 0.875rem; }
        h3, .h3 { font-size: 1.25rem; margin-bottom: 0.75rem; }
        h4, .h4 { font-size: 1.1rem; margin-bottom: 0.625rem; }
        h5, .h5 { font-size: 1rem; margin-bottom: 0.5rem; }
        
        /* Kích thước biểu tượng đồng nhất */
        .fa, .fas, .far, .fal, .fab {
            font-size: 0.95rem;
        }
        
        /* Padding và margin nhất quán */
        .p-4 { padding: 1rem !important; }
        .p-3 { padding: 0.75rem !important; }
        .p-2 { padding: 0.5rem !important; }
        .p-1 { padding: 0.25rem !important; }
        
        .m-4 { margin: 1rem !important; }
        .m-3 { margin: 0.75rem !important; }
        .m-2 { margin: 0.5rem !important; }
        .m-1 { margin: 0.25rem !important; }
        
        .my-4 { margin-top: 1rem !important; margin-bottom: 1rem !important; }
        .my-3 { margin-top: 0.75rem !important; margin-bottom: 0.75rem !important; }
        .my-2 { margin-top: 0.5rem !important; margin-bottom: 0.5rem !important; }
        .my-1 { margin-top: 0.25rem !important; margin-bottom: 0.25rem !important; }
        
        .mx-4 { margin-left: 1rem !important; margin-right: 1rem !important; }
        .mx-3 { margin-left: 0.75rem !important; margin-right: 0.75rem !important; }
        .mx-2 { margin-left: 0.5rem !important; margin-right: 0.5rem !important; }
        .mx-1 { margin-left: 0.25rem !important; margin-right: 0.25rem !important; }
        
        .py-4 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
        .py-3 { padding-top: 0.75rem !important; padding-bottom: 0.75rem !important; }
        .py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
        .py-1 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
        
        .px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
        .px-3 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
        .px-2 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
        .px-1 { padding-left: 0.25rem !important; padding-right: 0.25rem !important; }
        
        /* Thiết lập alert messages đồng nhất */
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
        }
        
        /* Màu sắc hệ thống */
        .text-primary, .text-blue-600 { color: #4f46e5 !important; }
        .bg-primary, .bg-blue-600 { background-color: #4f46e5 !important; }
        .border-primary, .border-blue-600 { border-color: #4f46e5 !important; }
        
        .text-secondary, .text-gray-600 { color: #6b7280 !important; }
        .bg-secondary, .bg-gray-600 { background-color: #6b7280 !important; }
        
        .bg-gray-50 { background-color: #f9fafb !important; }
        .bg-gray-100 { background-color: #f3f4f6 !important; }
        
        /* Border radius đồng nhất */
        .rounded-lg { border-radius: 0.5rem !important; }
        .rounded-md { border-radius: 0.375rem !important; }
        .rounded-sm { border-radius: 0.25rem !important; }
        .rounded-full { border-radius: 9999px !important; }
        
        /* Shadow tiêu chuẩn */
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }
        .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important; }
        .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; }
    </style>
</head>

<body class="bg-gray-50 font-sans" data-user-logged-in="{{ Auth::check() ? '1' : '0' }}">

    <!-- Header Navbar -->
    @include('frontend.layouts.header1')
    
    <!-- Main Content -->
    <main class="py-4">
        {{-- @yield('banner') --}}

        <div class="container">
           
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    @include('frontend.layouts.footer')

    <!-- Scripts -->
    @include('frontend.layouts.foot')

    @yield('scripts')
    
    <!-- Kiểm tra hiển thị modal đăng nhập từ URL parameter -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy URL hiện tại
        const urlParams = new URLSearchParams(window.location.search);
        
        // Kiểm tra nếu có tham số login=true
        if (urlParams.get('login') === 'true') {
            // Hiển thị modal đăng nhập
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            if (loginModal) {
                loginModal.show();
                
                // Xóa tham số login=true khỏi URL mà không reload trang
                const newUrl = window.location.pathname + 
                    window.location.search.replace(/[?&]login=true/, '').replace(/^&/, '?') + 
                    window.location.hash;
                window.history.replaceState({}, document.title, newUrl);
            }
        }
    });
    </script>
</body>

</html>
