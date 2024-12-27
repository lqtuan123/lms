{{-- <header class="relative wrapper bg-soft-primary !bg-[#edf2fc]">
    <nav class="navbar navbar-expand-lg center-nav navbar-light navbar-bg-light">
        <div class="container xl:flex-row lg:flex-row !flex-nowrap items-center">
            <div class="navbar-brand w-full">
                <a href="">
                    <img src="{{ $setting->logo }}" srcset="{{ $setting->logo }} 2x" alt="Logo LMS">
                </a>
            </div>
            <div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start">
                <div class="offcanvas-header xl:hidden lg:hidden flex items-center justify-between flex-row p-6">
                    <h3 class="text-white xl:text-[1.5rem] !text-[calc(1.275rem_+_0.3vw)] !mb-0">
                        {{ $setting->short_name }}</h3>
                    <button type="button"
                        class="btn-close btn-close-white mr-[-0.75rem] m-0 p-0 leading-none title_color transition-all duration-[0.2s] ease-in-out border-0 motion-reduce:transition-none before:text-[1.05rem] before:content-['\ed3b'] before:w-[1.8rem] before:h-[1.8rem] before:leading-[1.8rem] before:shadow-none before:transition-[background] before:duration-[0.2s] before:ease-in-out before:flex before:justify-center before:items-center before:m-0 before:p-0 before:rounded-[100%] hover:no-underline bg-inherit before:bg-[rgba(255,255,255,.08)] before:font-Unicons hover:before:bg-[rgba(0,0,0,.11)] focus:outline-0"
                        data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body xl:!ml-auto lg:!ml-auto flex flex-col !h-full">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="">Khóa học</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="">Bài tập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="">Quiz</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('front.book.index' )}}">Sách</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="">Tài nguyên</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="">Hồ sơ của tôi</a>
                        </li>
                    </ul>
                    <!-- /.navbar-nav -->
                    <div class="offcanvas-footer xl:hidden lg:hidden">
                        <div>
                            <a href="mailto:first.{{ $setting->email }}" class="link-inverse">{{ $setting->email }}</a>
                            <br> {{ $setting->hotline }}<br>
                            <nav class="nav social social-white mt-4">
                                <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]"
                                    href="{{ $setting->facebook }}">
                                    <img src="" class=" " />
                                </a>
                                <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]"
                                    href="{{ $setting->shopee }}">
                                    <img src="" class=" " />
                                </a>
                                <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]"
                                    href="{{ $setting->lazada }}">
                                    <img src="" class=" " />
                                </a>
                            </nav>
                            <!-- /.social -->
                        </div>
                    </div>
                    <!-- /.offcanvas-footer -->
                </div>
                <!-- /.offcanvas-body -->
            </div>
            <!-- /.navbar-collapse -->
            <div class="navbar-other w-full !flex !ml-auto">
                <ul class="navbar-nav !flex-row !items-center !ml-auto">
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvas-search"><i
                                class="uil uil-search before:content-['\eca5'] !text-[1.1rem]"></i></a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvas-user"><i
                                class="uil uil-user before:content-['\eca5'] !text-[1.1rem]"></i></a></li>
                    <li class="nav-item xl:hidden lg:hidden">
                        <button class="hamburger offcanvas-nav-btn"><span></span></button>
                    </li>
                </ul>
                <!-- /.navbar-nav -->
            </div>
            <!-- /.navbar-other -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Offcanvas User -->
    <div style="width:20rem" class="offcanvas offcanvas-end bg-light" id="offcanvas-user" data-bs-scroll="true">
        <div class="offcanvas-header flex items-center justify-between p-[1.5rem]">
            <h3 class="mb-0">{{ !$user ? 'ĐĂNG NHẬP' : 'THÔNG TIN TÀI KHOẢN' }} </h3>
            <button type="button"
                class="btn-close m-0 p-0 mr-[-.5rem] leading-none title_color transition-all duration-[0.2s] ease-in-out  border-0 motion-reduce:transition-none before:text-[1.05rem] before:content-['\ed3b'] before:w-[1.8rem] before:h-[1.8rem] before:leading-[1.8rem] before:shadow-none before:transition-[background] before:duration-[0.2s] before:ease-in-out before:flex before:justify-center before:items-center before:m-0 before:p-0 before:rounded-[100%] hover:no-underline bg-inherit before:bg-[rgba(0,0,0,.08)] before:font-Unicons hover:before:bg-[rgba(0,0,0,.11)] focus:outline-0"
                data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>

    </div>

    <!-- Offcanvas Search -->
    <div class="offcanvas offcanvas-top bg-light" id="offcanvas-search" data-bs-scroll="true">
        <div class="container flex !flex-row py-6">
            <form method="GET" action=""
                class="search-form relative before:content-['\eca5'] before:block before:absolute before:-translate-y-2/4 before:text-[1rem] before:text-[#343f52] before:z-[1] before:right-auto before:top-2/4 before:font-Unicons w-full before:left-0 focus:!outline-offset-0 focus:outline-0">
                <input name="searchdata" placeholder="Tìm kiếm khóa học..." id="search-form1" type="text"
                    class="form-control text-[0.8rem] !shadow-none pl-[1.75rem] !pr-[.75rem] border-0 bg-inherit m-0 block w-full font-medium leading-[1.7] text-[#60697b] px-4 py-[0.6rem] rounded-[0.4rem] focus:!outline-offset-0 focus:outline-0"
                    placeholder="Tìm kiếm khóa học, bài tập, hoặc tài nguyên">
            </form>
            <button type="button"
                class="btn-close leading-none title_color transition-all duration-[0.2s] ease-in-out p-0 border-0 motion-reduce:transition-none before:text-[1.05rem] before:content-['\ed3b'] before:w-[1.8rem] before:h-[1.8rem] before:leading-[1.8rem] before:shadow-none before:transition-[background] before:duration-[0.2s] before:ease-in-out before:flex before:justify-center before:items-center before:m-0 before:p-0 before:rounded-[100%] hover:no-underline bg-inherit before:bg-[rgba(0,0,0,.08)] before:font-Unicons hover:before:bg-[rgba(0,0,0,.11)] focus:outline-0"
                data-bs-dismiss="offcanvas" aria-label="Close" id='btn-close-search'></button>
        </div>
    </div>
</header> --}}

{{-- <header class="relative wrapper bg-soft-primary !bg-[#edf2fc]">
    <nav class="navbar navbar-expand-lg center-nav navbar-light navbar-bg-light">
      <div class="container xl:flex-row lg:flex-row !flex-nowrap items-center">
        <div class="navbar-brand w-full">
            <div class="navbar-brand w-full">
                <a href="">
                    <img src="{{ $setting->logo }}" srcset="{{ $setting->logo }} 2x" alt="Logo LMS">
                </a>
            </div>
        </div>
        <div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start">
          <div class="offcanvas-header xl:hidden lg:hidden flex items-center justify-between flex-row p-6">
            <h3 class="text-white xl:text-[1.5rem] !text-[calc(1.275rem_+_0.3vw)] !mb-0">{{$setting->short_name}}</h3>
            <button type="button" class="btn-close btn-close-white mr-[-0.75rem] m-0 p-0 leading-none title_color transition-all duration-[0.2s] ease-in-out border-0 motion-reduce:transition-none before:text-[1.05rem] before:content-['\ed3b'] before:w-[1.8rem] before:h-[1.8rem] before:leading-[1.8rem] before:shadow-none before:transition-[background] before:duration-[0.2s] before:ease-in-out before:flex before:justify-center before:items-center before:m-0 before:p-0 before:rounded-[100%] hover:no-underline bg-inherit before:bg-[rgba(255,255,255,.08)] before:font-Unicons hover:before:bg-[rgba(0,0,0,.11)] focus:outline-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body xl:!ml-auto lg:!ml-auto flex  flex-col !h-full">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{route('home')}}">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="">Bài tập</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="">Quiz</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('front.book.index')}}">Sách</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('front.tblogs.index') }}">Tương tác</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="">Hồ sơ của tôi</a>
                </li>
            </ul>
            <!-- /.navbar-nav -->
            <div class="offcanvas-footer xl:hidden lg:hidden">
              <div>
                <a href="mailto:first.{{$setting->email}}" class="link-inverse">{{$setting->email}}</a>
                <br> {{$setting->hotline}}<br>
                <nav class="nav social social-white mt-4">
                  <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]" href="{{$setting->facebook}}">
                    <img src="{{asset('frontend/assets_tp_tp/images/icon/facenho.png')}}" class=" "/>
                  </a>
                  <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]" href="{{$setting->shopee}}">
                    <img src="{{asset('frontend/assets_tp_tp/images/icon/shopeenho.png')}}" class=" "/>
                  </a>
                  <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]" href="{{$setting->lazada}}">
                    <img src="{{asset('frontend/assets_tp_tp/images/icon/laznho.png')}}" class=" "/>
                  </a>
                </nav>
                <!-- /.social -->
              </div>
            </div>
            <!-- /.offcanvas-footer -->
          </div>
          <!-- /.offcanvas-body -->
        </div>
        <!-- /.navbar-collapse -->
        <div class="navbar-other w-full !flex !ml-auto">
          <ul class="navbar-nav !flex-row !items-center !ml-auto">
            <li class="nav-item"><a class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-search"><i class="uil uil-search before:content-['\eca5'] !text-[1.1rem]"></i></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-user"> <i class="uil uil-user before:content-['\eca5'] !text-[1.1rem]"></i></a></li>
            
            {{-- <li class="nav-item ml-[.8rem]">
              <a class="nav-link !relative !flex !flex-row !items-center" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-cart">
                <i class="uil uil-shopping-cart !text-[1.1rem] before:content-['\ecbd']"></i>
                <span id="cart_qty_cls" class="badge badge-cart secondarybackgroundcolor w-[0.9rem] h-[0.9rem] !flex items-center justify-center !text-[0.55rem] p-0 rounded-[100%] secondarybackgroundcolor opacity-100"></span>
              </a>
            </li> --}}
{{-- <li class="nav-item xl:hidden lg:hidden">
              <button class="hamburger offcanvas-nav-btn"><span></span></button>
            </li>
          </ul>
          <!-- /.navbar-nav -->
        </div>
        <!-- /.navbar-other -->
      </div>
      <!-- /.container -->
    </nav>
    <!-- /.navbar -->
    <div class="offcanvas offcanvas-end bg-light" id="offcanvas-cart" data-bs-scroll="true">
      <div class="offcanvas-header flex items-center justify-between p-[1.5rem]">
        <h3 class="mb-0">Giỏ hàng</h3>
        <button type="button" class="btn-close m-0 p-0 mr-[-.5rem] leading-none title_color transition-all duration-[0.2s] ease-in-out  border-0 motion-reduce:transition-none before:text-[1.05rem] before:content-['\ed3b'] before:w-[1.8rem] before:h-[1.8rem] before:leading-[1.8rem] before:shadow-none before:transition-[background] before:duration-[0.2s] before:ease-in-out before:flex before:justify-center before:items-center before:m-0 before:p-0 before:rounded-[100%] hover:no-underline bg-inherit before:bg-[rgba(0,0,0,.08)] before:font-Unicons hover:before:bg-[rgba(0,0,0,.11)] focus:outline-0
        " data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
      </div>
      <!-- /.offcanvas-body -->
    </div>
    <!-- /.offcanvas -->
    <div style="width:20rem" class="offcanvas offcanvas-end bg-light" id="offcanvas-user" data-bs-scroll="true">
      <div class="offcanvas-header flex items-center justify-between p-[1.5rem]">
        <h3 class="mb-0">{{!$user?'ĐĂNG NHẬP':'THÔNG TIN TÀI KHOẢN'}} </h3>
        <button type="button" class="btn-close m-0 p-0 mr-[-.5rem] leading-none title_color transition-all duration-[0.2s] ease-in-out  border-0 motion-reduce:transition-none before:text-[1.05rem] before:content-['\ed3b'] before:w-[1.8rem] before:h-[1.8rem] before:leading-[1.8rem] before:shadow-none before:transition-[background] before:duration-[0.2s] before:ease-in-out before:flex before:justify-center before:items-center before:m-0 before:p-0 before:rounded-[100%] hover:no-underline bg-inherit before:bg-[rgba(0,0,0,.08)] before:font-Unicons hover:before:bg-[rgba(0,0,0,.11)] focus:outline-0
        " data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    
      </div>
      @if (!$user)
        @include('frontend.layouts.leftlogin')
      @else
        @include('frontend.layouts.leftaccount')
      @endif
     
      <!-- /.container -->
    </div>

    <div class="offcanvas offcanvas-top bg-light" id="offcanvas-search" data-bs-scroll="true">
      <div class="container flex !flex-row py-6">
        <form method = "GET" action=""  class="search-form relative before:content-['\eca5'] before:block before:absolute before:-translate-y-2/4 before:text-[1rem] before:text-[#343f52] before:z-[1] before:right-auto before:top-2/4 before:font-Unicons w-full before:left-0 focus:!outline-offset-0 focus:outline-0">
          <input name="searchdata" placeholder="Tìm kiếm sản phẩm..." id="search-form1" type="text" class="form-control text-[0.8rem] !shadow-none pl-[1.75rem] !pr-[.75rem] border-0 bg-inherit m-0 block w-full font-medium leading-[1.7] text-[#60697b] px-4 py-[0.6rem] rounded-[0.4rem] focus:!outline-offset-0 focus:outline-0" placeholder="Type keyword and hit enter">
        </form> 
        <button type="button" class="btn-close leading-none title_color transition-all duration-[0.2s] ease-in-out p-0 border-0 motion-reduce:transition-none before:text-[1.05rem] before:content-['\ed3b'] before:w-[1.8rem] before:h-[1.8rem] before:leading-[1.8rem] before:shadow-none before:transition-[background] before:duration-[0.2s] before:ease-in-out before:flex before:justify-center before:items-center before:m-0 before:p-0 before:rounded-[100%] hover:no-underline bg-inherit before:bg-[rgba(0,0,0,.08)] before:font-Unicons hover:before:bg-[rgba(0,0,0,.11)] focus:outline-0" data-bs-dismiss="offcanvas" aria-label="Close">

        </button>
      </div>
      <!-- /.container -->
    </div>

    
    <!-- /.offcanvas -->
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
</header>
