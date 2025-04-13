 <?php
 
 $detail = \App\Models\SettingDetail::find(1);
 $user = auth()->user();
 
 ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     @include('frontend.layouts.head')
     <style>
         /* .blog-container img {
            max-width: 100%;
            height: auto;
        } */
     </style>
 </head>

 <body class="theme-color-10">


     <!-- loader start -->

     <!-- loader end -->


     <!-- header start -->
     @include('frontend.layouts.header')
     <!-- header end -->
     <!-- breadcrumb -->

     <!-- breadcrumb -->
     <!-- error display -->
     <div>
         @if (session('error'))
             <div class="alert alert-danger alert-dismissible fade show" role="alert">
                 {{ session('error') }}
                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
             </div>
         @endif
     </div>
     <!-- error display -->
     @yield('content')



     <!-- footer section start -->
     @include('frontend.layouts.footer')
     <!-- footer section end -->


     <!--modal popup start-->
     <!-- include('frontend.layouts.home_popup') -->
     <!--modal popup end-->


     <!-- Quick-view modal popup start-->

     <!-- Quick-view modal popup end-->



     <!-- tap to top -->
     {{-- <div class="tap-top top-cls">
        <div>
            <i class="fa fa-angle-double-up"></i>
        </div>
    </div> --}}
     <!-- tap to top end -->
     @include('frontend.layouts.foot')

     @yield('scripts')
     @yield('footscripts')
 </body>

 </html>
