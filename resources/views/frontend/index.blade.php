 <?php
 
 $setting = \app\Models\SettingDetail::find(1);
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
 <style>
  .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
  }
</style>
 @endsection
 @section('content')
 <div class="container">
     <!--  blog section -->
     {{-- @include('frontend.layouts.home_blog') --}}
     
     @include('frontend.layouts.book')
 </div>
 @endsection
 @section('scripts')
     <script src="{{ asset('frontend/assets/js/timer.js') }}"></script>
 @endsection
