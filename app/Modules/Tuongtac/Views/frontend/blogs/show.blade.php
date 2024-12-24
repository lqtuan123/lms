@extends('Tuongtac::frontend.blogs.body')
<?php

use Carbon\Carbon;
?>
@section('inner-content')
            
 

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<?php
    $images = json_decode($post->photo, true); // Giải mã JSON thành mảng
    $json_photo = json_encode($images);

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
    $adsense_code = '<ins class="adsbygoogle"
        style="display:block; text-align:center;"
        data-ad-layout="in-article"
        data-ad-format="fluid"
        data-ad-client="ca-pub-5437344106154965"
        data-ad-slot="3375673265"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>';

    $content = $post->content;
    // Tìm vị trí của thẻ <p> đầu tiên
    $position = strpos($content, '</p>', strlen($content) / 2); // Sau thẻ </p> gần giữa

    // Nếu tìm thấy vị trí, chèn mã AdSense
    if ($position !== false) {
        $new_content = substr_replace($content, $adsense_code, $position + 4, 0); // +4 vì thêm sau </p>
    } else {
        // Nếu không có <p>, chèn vào giữa
        $new_content = $content . $adsense_code ;
    }


?>
<div class="back-button">
    <a href="{{ url()->previous() }}" class=" btn-secondary">
        ← Quay lại
    </a>
</div>
<div class="post-card {{$post->status==0? 'postprivate':''}}" style="position: relative; padding-top:32px; margin-top:5px">
    <!-- Hình ảnh đầu bài -->
    @if(\Auth::id() == $post->user_id|| (auth()->id() && auth()->user()->role=='admin'))
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
        <img src="{{$images?$images[0]:''}}"  onclick=" openLightbox('{{ $json_photo }}', 0)"   alt="{{$post->title}}">
    </div>
    <div class="thumbnail-container">
        @foreach($images as $image)
            @if($image != "")
                <img  class="thumbnail" onclick=" openLightbox('{{ $json_photo }}', {{$loop->index }})"  src="{{ $image }}" alt="Hình ảnh rao vặt">
            @else
                <div style="height:20px">&nbsp;</div>
            @endif
        @endforeach
    </div>
    @endif
     <!-- Nút "Edit" và "Delete" nếu là tác giả -->
  
    <!-- Tác giả -->
    <div class="post-author">
        <a href="{{$post->user_url}}"><img src="{{$post->author->photo}}" alt="Author" class="author-avatar"></a>
        <div class="author-info">
            <a href="{{$post->user_url}}"> <h3>{{$post->author->full_name}}</h3> </a>
            <p> {{ Carbon::parse($post->created_at)->format('d/m/Y') }} </p>
        </div>
    </div>

    <!-- Nội dung bài viết -->
    <div class="post-content">
        <h2 class="post-title">
            <a href="#">{{ $post->title}}</a>
        </h2>
        <div class="post-tags">
        @foreach($post->tags  as $tag)
            <span>#{{$tag->title}}</span>   
        @endforeach
        </div>
        <div class ="post-content">
            <?php echo $new_content ; ?>
        </div>
    </div>

    <?php
    if(isset($link_download))
    {
        echo $link_download;
    }
    ?>

    <!-- Hành động -->
    <?php echo $post->actionbar ; ?>
</div>
</div>
<div id ="comment-box-{{$post->id}}" class="comment-box" style=" margin-bottom: 20px;">
<?php echo $post->commenthtml; ?>
</div>

 <!-- Lightbox cho ảnh lớn -->
 <div id="lightbox" class="lightbox" onclick="closeLightbox()" style="display: none;">
    <span class="close" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" class="lightbox-img" onclick="event.stopPropagation()" src="">
    <button class="next" onclick="event.stopPropagation(); changeImage(1);">&#10095;</button>
    <button class="prev" onclick="event.stopPropagation(); changeImage(1);">&#10094;</button>
   
</div>

@endsection
 
@section('botscript')

<?php echo $script_actionbar; ?>
<script>
 let currentImageIndex = 0;
let images = [];

function openLightbox(imageList, index) {
    images = JSON.parse(imageList);
    currentImageIndex = index;
    document.getElementById("lightbox-img").src = images[currentImageIndex];
    document.getElementById("lightbox").style.display = "flex";
}

function closeLightbox() {
    document.getElementById("lightbox").style.display = "none";
}

function changeImage(direction) {
    currentImageIndex += direction;
    if (currentImageIndex < 0) {
        currentImageIndex = images.length - 1;
    } else if (currentImageIndex >= images.length) {
        currentImageIndex = 0;
    }
    document.getElementById("lightbox-img").src = images[currentImageIndex];
}
</script>
@endsection