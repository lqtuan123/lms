<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
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
<link rel="shortcut icon" href="{{ $detail->icon }}" type="image/x-icon">

<!-- Title -->
<title>{{ ($page_up_title ?? '') . ' ' . $detail->web_title }}</title>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
  integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

<!-- Stylesheets -->

<link rel="stylesheet" href="{{ asset('frontend/assets_f/style.css') }}">



@yield('css')
@yield('scriptop')


