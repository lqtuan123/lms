 <?php
 
  $setting =\app\Models\SettingDetail::find(1);
  $user = auth()->user();

?>
{{--
@extends('frontend.layouts.master')
@section('head_css')
@endsection
@section('content')
@include('frontend.layouts.book')
@endsection --}}

@extends('frontend.layouts.master1')
@section('css')
    
     
@endsection
@section('content')
   <!-- Home slider -->
 
   <!-- include('frontend.layouts.homeslider') -->
    <!-- Home slider end -->


    <!-- service section start -->
    <!--  include('frontend.layouts.home_service') -->
    <!-- service section end -->


    <!-- product deal section start -->
    <!-- include('frontend.layouts.home_dealday') -->
    <!-- product deal section start -->
   

    <!-- banner section start -->
     <!-- include('frontend.layouts.home_banner') -->
    <!-- banner section end -->

    <!-- slider and product -->
     <!-- include('frontend.layouts.product_slider') -->
    <!-- slider and product -->



    <!-- banner section start -->
 
    <!-- banner section end -->


    <!-- collection banner -->
     <!-- include('frontend.layouts.home_banner3') -->
    <!-- collection banner end -->


    <!-- Tab product -->
  
    <!-- Tab product end -->
    <!--  blog section -->
     @include('frontend.layouts.home_blog')
    <!--  blog section end-->

    <!--  logo section -->
     <!-- include('frontend.layouts.home_logobrand') -->
    <!--  logo section end-->
    @include('frontend.layouts.book')
@endsection
@section('scripts')
<script src="{{asset('frontend/assets/js/timer.js')}}"></script>
@endsection