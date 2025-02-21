
{{-- <?php
$categories = \App\Models\Category::where('status', 'active')->orderBy('title', 'ASC')->get();
$detail = \App\Models\SettingDetail::find(1); ?>

<header class="header-style-5">
    <div class="mobile-fix-option"></div>
    <div class="top-header top-header-dark">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="header-contact">
                        
                    </div>
                </div>
                <?php
                $user = auth()->user();
                
                if ($user) {
                    $title = 'Tài khoản';
                    $link = route('front.profile');
                    $linkpro = 'logout.php';
                    $titlepro = 'Đăng xuất';
                } else {
                    $link = route('front.register');
                    $linkpro = route('front.login');
                    $title = 'Đăng ký';
                    $titlepro = 'Đăng nhập';
                }
                ?>
                <div class="col-lg-6 text-end">
                    <ul class="header-dropdown">

                        <li class="onhover-dropdown mobile-account"> <i class="fa fa-user" aria-hidden="true"></i>


                            <a href="{{ $link }}"> {{ $title }} </a> |
                            @if ($user)
                                <a href=""
                                    onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">

                                    <span> {{ $titlepro }} </span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf

                                </form>
                            @else
                                <a href="{{ $linkpro }}"> {{ $titlepro }} </a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="main-menu">
                    <div class="menu-left">
                        <div class="navbar d-block d-xl-none">
                            <a href="javascript:void(0)">
                                <div class="bar-style" id="toggle-sidebar-res"><i class="fa fa-bars sidebar-bar"
                                        aria-hidden="true"></i>
                                </div>
                            </a>
                        </div>


                        <div class="brand-logo">
                            <a href="{{ route('home') }}"><img src="{{ $detail->logo }}"
                                    class="img-fluid blur-up lazyload" alt="{{ $detail->keyword }}"></a>
                        </div>
                    </div>
                    <div>
                        <form method = "GET" action="" class="form_search" role="form">
                            @csrf
                            <input id="query search-autocomplete" type="search" name="searchdata"
                                placeholder="Tìm kiếm sản phẩm..." class="nav-search nav-search-field"
                                aria-expanded="true">
                            <button type="submit" name="nav-submit-button" class="btn-search">
                                <i class="fa fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="menu-right pull-right">
                        <nav class="text-start">
                            <div class="toggle-nav"><i class="fa fa-bars sidebar-bar"></i></div>
                        </nav>
                        <div>
                            <div class="icon-nav">
                                <ul>
                                    <li class="onhover-div d-xl-none d-inline-block mobile-search">
                                        <div><img src="{{ asset('frontend/assets/images/icon/search.png') }}"
                                                onclick="openSearch()" class="img-fluid blur-up lazyload"
                                                alt=""> <i class="fa fa-search" onclick="openSearch()"></i>
                                        </div>
                                        <div id="search-overlay" class="search-overlay">
                                            <div> <span class="closebtn" onclick="closeSearch()"
                                                    title="Close Overlay">×</span>
                                                <div class="overlay-content">
                                                    <div class="container">
                                                        <div class="row">
                                                            <div class="col-xl-12">
                                                                <form method = "POST" action="">
                                                                    <div class="form-group">
                                                                        <input type="text" class="form-control"
                                                                            id="exampleInputPassword1"
                                                                            placeholder="Search a Product">
                                                                    </div>
                                                                    <button type="submit" class="btn btn-primary"><i
                                                                            class="fa fa-search"></i></button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="onhover-div mobile-cart">

                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bottom-part bottom-light">
        <div class="container">
            <div class="row">
                <div class="col-xl-3">
                    <div class="category-menu d-none d-xl-block h-100" >
                        <div id="toggle-sidebar" class="toggle-sidebar" style="background-color: #add8e6">
                            <i class="fa fa-bars sidebar-bar"></i>
                            <h5 class="mb-0 ">Danh mục</h5>
                        </div>
                    </div>
                    <div class="sidenav fixed-sidebar marketplace-sidebar" style="z-index:1000">
                        <nav>
                            <div>
                                <div class="sidebar-back text-start d-xl-none d-block"><i class="fa fa-angle-left pe-2"
                                        aria-hidden="true"></i> Back</div>
                            </div>
                            <ul id="sub-menu" class="sm pixelstrap sm-vertical">
                                @foreach ($categories as $cat)
                                    <li> <a href="">{{ $cat->title }}</a>
                                @endforeach


                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="main-nav-center">
                        <nav class="text-start">
                            <!-- Sample menu definition -->
                            <ul id="main-menu" class="sm pixelstrap sm-horizontal">
                                <li>
                                    <div class="mobile-back text-end">Back<i class="fa fa-angle-right ps-2"
                                            aria-hidden="true"></i></div>
                                </li>
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="{{ route('front.book.index') }}">Sách</a></li>
                                <li><a href="{{ route('front.tblogs.index') }}">Tương tác</a></li>
                                <li><a href="">Đề thi</a></li>
                                <li><a href="">Sự kiện</a></li>
                                <li><a href="">Liên hệ</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header> --}}

<?php
$categories = \App\Models\Category::where('status', 'active')->orderBy('title', 'ASC')->get();
$detail = \App\Models\SettingDetail::find(1); ?>

<header class="header-style-5">
    <div class="mobile-fix-option"></div>
    <div class="top-header top-header-dark">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="header-contact">
                    </div>
                </div>
                <?php
                $user = auth()->user();
                
                if ($user) {
                    $title = 'Tài khoản';
                    $link = route('front.profile');
                    $linkpro = 'logout.php';
                    $titlepro = 'Đăng xuất';
                } else {
                    $link = route('front.register');
                    $linkpro = route('front.login');
                    $title = 'Đăng ký';
                    $titlepro = 'Đăng nhập';
                }
                ?>
                <div class="col-lg-6 text-end">
                    <ul class="header-dropdown">
                        <li class="onhover-dropdown mobile-account"> <i class="fa fa-user" aria-hidden="true"></i>
                            <a href="{{ $link }}"> {{ $title }} </a> |
                            @if ($user)
                                <a href=""
                                    onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                    <span> {{ $titlepro }} </span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @else
                                <a href="{{ $linkpro }}"> {{ $titlepro }} </a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="main-menu">
                    <div class="menu-left">
                        <div class="navbar d-block d-xl-none">
                            <a href="javascript:void(0)">
                                <div class="bar-style" id="toggle-sidebar-res"><i class="fa fa-bars sidebar-bar"
                                        aria-hidden="true"></i>
                                </div>
                            </a>
                        </div>
                        <div class="brand-logo">
                            <a href="{{ route('home') }}"><img src="{{ $detail->logo }}"
                                    class="img-fluid blur-up lazyload" alt="{{ $detail->keyword }}"></a>
                        </div>
                    </div>
                    <div class="menu-right pull-right">
                        <nav class="text-start">
                            <div class="toggle-nav"><i class="fa fa-bars sidebar-bar"></i></div>
                        </nav>
                        <div>
                            <div class="icon-nav">
                                <ul>
                                    <li class="onhover-div mobile-cart">
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <div class="bottom-part bottom-light">
        <div class="container">
            <div class="row">
                <div class="col-xl-3">
                    <div class="category-menu d-none d-xl-block h-100" >
                        <div id="toggle-sidebar" class="toggle-sidebar" style="background-color: #add8e6">
                            <i class="fa fa-bars sidebar-bar"></i>
                            <h5 class="mb-0 ">Danh mục</h5>
                        </div>
                    </div>
                    <div class="sidenav fixed-sidebar marketplace-sidebar" style="z-index:1000">
                        <nav>
                            <div>
                                <div class="sidebar-back text-start d-xl-none d-block"><i class="fa fa-angle-left pe-2"
                                        aria-hidden="true"></i> Back</div>
                            </div>
                            <ul id="sub-menu" class="sm pixelstrap sm-vertical">
                                @foreach ($categories as $cat)
                                    <li> <a href="">{{ $cat->title }}</a>
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="main-nav-center">
                        <nav class="text-start">
                            <ul id="main-menu" class="sm pixelstrap sm-horizontal">
                                <li>
                                    <div class="mobile-back text-end">Back<i class="fa fa-angle-right ps-2"
                                            aria-hidden="true"></i></div>
                                </li>
                                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li><a href="{{ route('front.book.index') }}">Sách</a></li>
                                <li><a href="{{ route('front.tblogs.index') }}">Tương tác</a></li>
                                <li><a href="">Đề thi</a></li>
                                <li><a href="">Sự kiện</a></li>
                                <li><a href="">Liên hệ</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

