<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="author" content="{{ $detail->short_name ?? 'Default Author' }}">
<meta name="robots" content="INDEX,FOLLOW">
<meta name="copyright" content="{{ $detail->site_url ?? url('/') }}">
<meta name="revisit-after" content="1 days">
<meta name="keywords" content="{{ $keyword ?? ($detail->keyword ?? '') }}">
<meta name="description" content="{{ strip_tags($description ?? ($detail->memory ?? '')) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">


<!-- Open Graph (Facebook) -->
<meta property="og:title" content="{{ $page_up_title ?? $detail->web_title }}">
<meta property="og:description" content="{{ strip_tags($description ?? ($detail->memory ?? '')) }}">
<meta property="og:image" content="{{ $ogimage ?? $detail->logo }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">

<!-- Twitter Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta property="twitter:domain" content="{{ $detail->site_url ?? url('/') }}">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="{{ $page_up_title ?? $detail->web_title }}">
<meta name="twitter:description" content="{{ strip_tags($description ?? ($detail->memory ?? '')) }}">
<meta name="twitter:image" content="{{ $ogimage ?? $detail->logo }}">

<!-- Favicon -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Bootstrap CSS and JS for modal functionality -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="shortcut icon" href="{{ $detail->icon }}" type="image/x-icon">

<!-- Title -->
<title>@yield('title', 'ReadSocial - Đọc sách và học tập cùng cộng đồng')</title>

<!-- Google Fonts -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReadSocial - Đọc sách và học tập cùng cộng đồng</title>

    <style>
        /* Custom CSS for elements that need more specific styling */
        .search-bar {
            transition: all 0.3s ease;
        }

        .search-bar:focus-within {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .category-card:hover {
            background-color: #f3f4f6;
        }

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

        @media (max-width: 768px) {
            .mobile-menu {
                display: none;
            }

            .mobile-menu.active {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                z-index: 50;
                padding: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
        }
    </style>
</head>
<!-- Stylesheets -->
{{-- <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}"> --}}

<!-- PDF.js cho trang đọc sách -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>

@yield('topcss')
@yield('css')
@yield('scriptop')
