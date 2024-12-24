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
        <form action="{{ route('front.tblogs.index') }}" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Tìm kiếm bài viết..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-search">Tìm kiếm</button>
        </form>

        <!-- Nút thêm bài viết -->
        <a href="{{ route('front.groupblog.create',$page->id) }}" class="btn btn-add">Viết bài</a>
    </div>
    {{-- danh sách bai viet --}}
    <?php
        $vitri = 0;

        ?>

 
    @foreach($surveys as $survey)
        <div class="post-card poll-card " style="position: relative; padding-top:32px; margin-top:5px">
                <!-- Hình ảnh đầu bài -->
                <div class="action-buttons" style="position: absolute; top: -0px; right: 4px;z-index:1000">
                    @if(\Auth::id() === $survey->user_id || (auth()->id() && auth()->user()->role=='admin'))
                            <!-- Nút Edit -->
                            <a href="{{ route('front.surveys.editsurvey', $survey->id) }}?frompage={{$page->slug}}"  style=" "><button class="deletebtn"> <i class="feather icon-feather-edit icon-extra-small  " style="background:white"></i> </button></a>
                            <form action="{{ route('front.surveys.destroysurvey', $survey->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="deletebtn" onclick="return confirm('Bạn có chắc muốn xóa nhóm thăm dò này không?');"><i class="feather icon-feather-trash icon-extra-small  " style="background:white"></i></button>
                            </form>
                    @endif
                </div>
            <div class="post-item">
                <!-- Nội dung bài viết -->
                <div class="post-content">
                    <span> Tham gia khảo sát và xem kết quả </span>
                    <h2 class="post-title">
                        <a href="{{route('front.surveys.show',[$survey->slug])}}">
                            <i class="feather icon-feather-pocket icon-extra-small  " style="background:white"></i> 
                            
                            {{ $survey->name}}
                        </a>
                    </h2>
                    <?php 
                     $userIds = json_decode($survey->user_ids );
                     $dem = 0;
                     if ( $userIds)
                        $dem = count($userIds);
                     ?>
                    <span> Số người đã tham gia: {{ $dem}} </span>
                </div>
            </div>
        </div>
    @endforeach
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
            if($vitri % 6 == 0)
                echo '<div class="post-card ">'.$adsense_code. '</div>';

    ?>
        <div class="post-card {{$post->status==0? 'postprivate':''}}" style="position: relative; padding-top:32px; margin-top:5px">
                <!-- Hình ảnh đầu bài -->
                <div class="action-buttons" style="position: absolute; top: -0px; right: 4px;z-index:1000">
                    <a   style=" "><button onclick="openPopup('{{$post->slug}}')" class="deletebtn"> <i class="feather icon-feather-eye icon-extra-small  " style="background:white"></i> </button></a>
                            
                    @if(\Auth::id() === $post->author->id)
                    
                            <!-- Nút Edit -->
                            @if($post->status == 1)
                                <a href="{{ route('front.tblogs.status', $post->id) }}" title=" ẩn bài viết " style=" "><button class="deletebtn"> <i class="feather icon-feather-x-circle icon-extra-small  " style="background:white"></i> </button></a>
                            @else
                                <a href="{{ route('front.tblogs.status', $post->id) }}" title=" công khai bài viết " style=" "><button class="deletebtn"> <i class="feather icon-feather-check-circle icon-extra-small  " style="background:white"></i> </button></a>
                    
                            @endif
                            <a href="{{ route('front.tblogs.edit', $post->id) }}?frompage={{$page->slug}}"  style=" "><button class="deletebtn"> <i class="feather icon-feather-edit icon-extra-small  " style="background:white"></i> </button></a>
                            
                            <!-- Nút Delete -->
                            <form action="{{ route('front.tblogs.destroy', $post->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="deletebtn" onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');"><i class="feather icon-feather-trash icon-extra-small  " style="background:white"></i></button>
                            </form>
                    
                    @endif
                </div>
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
    <!-- Popup Modal -->
    <!-- Popup Modal -->
    <!-- Popup Modal -->
    <div id="contentPopup" class="popup-modal" onclick="closePopup()">
        <div class="popup-content" onclick="event.stopPropagation()"> <!-- Ngăn sự kiện click lan truyền -->
            <button class="close-popup" onclick="closePopup()">×</button>
            <h2 id="popup-title">Tiêu đề bài viết</h2>
            <p id="popup-body">Nội dung bài viết sẽ hiển thị ở đây...</p>
        </div>
    </div>
      
 
@endsection
@section('botscript')
<?php echo $script_actionbar; ?>
@endsection