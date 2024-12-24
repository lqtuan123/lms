@extends('Tuongtac::frontend_quyco.blogs.body')
@section('inner-content')
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
<div class="container">
    <div class="user-post-card">
        <div class="avatar">
            <img src="{{$user->photo}}" alt="{{$user->full_name}}">
        </div>
        <div class="info">
            <h2 class="name">{{$user->full_name}}</h2>
            <p class="points">Điểm thành viên: <span>{{$userpage->point}}</span></p>
            <p class="posts">Số bài viết: <span>{{$post_count}}</span></p>
            <p class="recommend">Số bài giới thiệu: <span>{{$post_recommend}}</span></p>
        </div>
    </div>
    <?php
    $vitri = 0;

    ?>
    @foreach($posts as $post)

    <?php
            $images = json_decode($post->photo, true); // Giải mã JSON thành mảng
            // dd($images[0]);
            $createdAt = Carbon::parse($post->created_at); // Thay đổi $comment thành đối tượng bạn đang sử dụng
            $diffInMinutes = $createdAt->diffInMinutes();
            $diffInHours = $createdAt->diffInHours();
            $diffInDays = $createdAt->diffInDays();
            $thoigian = "";
            if ($diffInMinutes < 60) {
                $thoigian= $diffInMinutes . ' phút trước';
            } elseif ($diffInHours < 24) {
                $thoigian= $diffInHours . ' tiếng trước';
            } else {
                $thoigian= $diffInDays . ' ngày trước';
            }
            $vitri++;
            if($vitri % 10 == 0)
                echo '<div class="post-card ">'.$adsense_code. '</div>';

    ?>
        <div class="post-card {{$post->status==0? 'postprivate':''}}" style="position: relative; padding-top:32px; margin-top:5px">
                <!-- Hình ảnh đầu bài -->
                @if(\Auth::id() === $post->author->id)
                    <div class="action-buttons" style="position: absolute; top: -0px; right: 4px;z-index:1000">
                        <!-- Nút Edit -->
                        @if($post->status == 1)
                            <a href="{{ route('front.tblogs.status', $post->id) }}" title=" ẩn bài viết " style=" "><button class="deletebtn"> <i class="feather icon-feather-x-circle icon-extra-small  " style="background:white"></i> </button></a>
                        @else
                            <a href="{{ route('front.tblogs.status', $post->id) }}" title=" công khai bài viết " style=" "><button class="deletebtn"> <i class="feather icon-feather-check-circle icon-extra-small  " style="background:white"></i> </button></a>
                 
                        @endif
                        <a href="{{ route('front.tblogs.edit', $post->id) }}"  style=" "><button class="deletebtn"> <i class="feather icon-feather-edit icon-extra-small  " style="background:white"></i> </button></a>
                        
                        <!-- Nút Delete -->
                        <form action="{{ route('front.tblogs.destroy', $post->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="deletebtn" onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');"><i class="feather icon-feather-trash icon-extra-small  " style="background:white"></i></button>
                        </form>
                    </div>
                @endif
            <div class="post-item">
                
                @if($images)
                <div class="post-image">
                    <a href="{{route('front.tblogs.show',$post->slug)}}">
                         <img src="{{$images?$images[0]:''}}" alt="{{$post->title}}">
                    </a>
                </div>
                @endif
                 <!-- Nút "Edit" và "Delete" nếu là tác giả -->
              
                <!-- Tác giả -->
                <div class="post-author">
                    <a href="{{$post->user_url}}"><img src="{{$post->author->photo}}" alt="Author" class="author-avatar"> </a>
                    <div class="author-info">
                        <h3> <a href="{{$post->user_url}}">{{$post->author->full_name}} </a></h3>
                        <p> {{ Carbon::parse($post->created_at)->format('d/m/Y') }} </p>
                    </div>
                </div>
    
                <!-- Nội dung bài viết -->
                <div class="post-content">
                    <h2 class="post-title">
                        <a href="{{route('front.tblogs.show',$post->slug)}}">{{ $post->title}}</a>
                    </h2>
                    <div class="post-tags">
                    @foreach($post->tags  as $tag)
                       <a href="{{route('front.tblogs.tag',$tag->slug)}}"> <span>#{{$tag->title}}</span>   </a>
                    @endforeach
                    </div>
                </div>
    
                <!-- Hành động -->
                <?php echo $post->actionbar ; ?>
            </div>
        </div>
        <div id ="comment-box-{{$post->id}}" class="comment-box" style="display: none; margin-bottom: 20px;">
          <?php echo $post->commenthtml; ?>
        </div>
    @endforeach
    <div class="col-12 mt-5 mb-5 d-flex justify-content-center">
        {{$posts->links('vendor.pagination.simple_itcctv')}}
    </div>
</div>
@endsection


@section('botscript')
<?php echo $script_actionbar; ?>
@endsection