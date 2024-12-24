@extends('frontend_quyco.layouts.master')
@section('head_css')
<link rel="stylesheet" href="{{asset('frontend/css/custom7.css')}}" type="text/css" />

@yield('topcss')
<style>
 
</style>
@endsection
@section('content')
@include('frontend_quyco.layouts.page_title')
<div class="container">
    @include('frontend_quyco.layouts.notification')
    <div class="mcontainer dev">
 
        <!-- Left Menu -->
        @include('Tuongtac::frontend_quyco.surveys.left')

        <!-- Main Content -->
        <main class="main-content">
            @yield('inner-content')
        </main>

        <!-- Right Menu -->
        @include('Tuongtac::frontend_quyco.surveys.right')

    </div>
    <div id="spinner" style="display: none;">
        <div class="spinner"></div>
    </div>
   
</div>
@endsection

@section('footscripts')
@yield('botscript')
 
@endsection