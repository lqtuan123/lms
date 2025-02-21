@extends('Tuongtac::frontend.pages.body')
<?php

use Carbon\Carbon;

$adsense_code = '<ins class="adsbygoogle"
            style="display:block; text-align:center;"
            data-ad-layout="in-article"
            data-ad-format="fluid"
            data-ad-client="ca-pub-5437344106154965"
            data-ad-slot="3375673265"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>';
 

?>
@section('inner-content')
            
    <!-- Thanh bar trên danh sách post -->
    <div class="top-bar">
        <!-- Nút tìm kiếm -->
        {{-- <form action="{{ route('front.tblogs.index') }}" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Tìm kiếm bài viết..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-search">Tìm kiếm</button>
        </form> --}}

        <!-- Nút thêm bài viết -->
        <a href="{{ route('front.surveys.create',[$page->id,'page']) }}" class="btn btn-add">Thêm survey</a>
    </div>
    {{-- danh sách bai viet --}}
    <?php
        $vitri = 0;

        ?>
    @foreach($surveys as $survey)

    <?php
           

    ?>
        <div class="post-card  " style="position: relative; padding-top:32px; margin-top:5px">
                <!-- Hình ảnh đầu bài -->
                <div class="action-buttons" style="position: absolute; top: -0px; right: 4px;z-index:1000">
                        
                    @if( (auth()->id() && auth()->user()->role=='admin'))
                    
                            <!-- Nút Edit -->
                            
                            <a href="{{ route('front.surveys.editsurvey', $survey->id) }}?frompage={{$page->slug}}"  style=" "><button class="deletebtn"> <i class="feather icon-feather-edit icon-extra-small  " style="background:white"></i> </button></a>
                            
                             
                            <form action="{{ route('front.surveys.destroysurvey', $survey->id) }}" method="POST" style="display: inline;">
                                @csrf
                               
                                <button type="submit" class="deletebtn" onclick="return confirm('Bạn có chắc muốn xóa nhóm thăm dò này không?');"><i class="feather icon-feather-trash icon-extra-small  " style="background:white"></i></button>
                            </form>
                    
                    @endif
                </div>
            <div class="post-item">
                
               
                 <!-- Nút "Edit" và "Delete" nếu là tác giả -->
              
               
    
                <!-- Nội dung bài viết -->
                <div class="post-content">
                    <h2 class="post-title">
                        <a href="{{route('front.surveys.show',[$survey->slug])}}">{{ $survey->name}}</a>
                    </h2>
                   
                     
                </div>
     
            </div>
        </div>
       
    @endforeach
    <div class="col-12 mt-5 mb-5 d-flex justify-content-center">
        {{$surveys->links('vendor.pagination.simple_itcctv')}}
    </div>
 
      
    @endsection
    @section('botscript')
 
    @endsection