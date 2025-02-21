@extends('frontend.layouts.master1')
@section('css')
{{-- <link rel="stylesheet" href="{{asset('frontend/assets/css/custom8.css')}}" type="text/css" /> --}}
<link rel="stylesheet" href="{{ asset('frontend/assets/css/custom8.css') }}" type="text/css" />

@yield('topcss')
<style>
 
</style>
@endsection
@section('content')

<div class="container">
    @include('frontend.layouts.notification')
    <div class="mcontainer dev">
 
        <!-- Left Menu -->
        @include('Tuongtac::frontend.surveys.left')

        <!-- Main Content -->
        <main class="main-content">
            @yield('inner-content')
        </main>

        <!-- Right Menu -->
        @include('Tuongtac::frontend.surveys.right')

    </div>
    <div id="spinner" style="display: none;">
        <div class="spinner"></div>
    </div>
   
</div>
@endsection

@section('footscripts')
@yield('botscript')
 
@endsection