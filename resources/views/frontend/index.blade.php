 <?php
 
 $setting = \app\Models\SettingDetail::find(1);
 $user = auth()->user();
 
 ?>


 @extends('frontend.layouts.master1')

 @section('css')
 @endsection
 @section('content')
     @include('frontend.layouts.bannertop')
     <div class="container">


         @include('frontend.layouts.book')
         @include('frontend.layouts.post')
         @include('frontend.layouts.group')

     </div>
 @endsection
 @section('scripts')
     <script src="{{ asset('frontend/assets/js/timer.js') }}"></script>
 @endsection
