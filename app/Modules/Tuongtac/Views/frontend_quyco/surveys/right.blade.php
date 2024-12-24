 
<?php
$popularPosts = \App\Modules\Tuongtac\Models\TBlog::orderBy('hit','desc')->limit(10) 
       ->get();

$newusers = \App\Models\User::orderBy('id','desc')->limit(5)->get();
?>
@php
use Illuminate\Support\Str;
@endphp
<aside class="right-menu">
   
   <div class="section">
   
       <!-- rightbar -->
      
   
       <div class="popular-posts">
           <h3>Bài viết phổ biến</h3>
           <ul>
               @foreach($popularPosts as $post)
               <?php
                       $images = json_decode($post->photo, true); // Giải mã JSON thành mảng
                       if (!$images)
                       {
                           $thumbnail_url = "https://itcctv.vn/images/profile-8.jpg";
                       }
                       else {
                           $thumbnail_url = $images[0];
                           
                       }
               ?>
                   <li>
                       <a href="{{ route('front.tblogs.show', $post->slug) }}">
                           <div class="popular-post-item">
                               <img src="{{  $thumbnail_url }}" alt="{{ $post->title }}" class="popular-post-thumbnail">
                               <div class="popular-post-title">{{Str::limit($post->title, 60) }}</div>
                           </div>
                       </a>
                   </li>
               @endforeach
           </ul>
       </div>
       <ins class="adsbygoogle"
           style="display:block"
           data-ad-client="ca-pub-5437344106154965"
           data-ad-slot="7114573880"
           data-ad-format="auto"
           data-full-width-responsive="true"></ins>
           <script>
               (adsbygoogle = window.adsbygoogle || []).push({});
           </script>
   </div>
   
   <div class="section">
       <div class="popular-posts">
           <h3>Người dùng mới</h3>
           <ul>
               @foreach($newusers as $newuser)
               
                        
                   <li>
                       <a href=" ">
                           <div class="popular-post-item">
                               <img src="{{ $newuser->photo}}" alt="{{  $newuser->full_name }}" class="popular-post-thumbnail">
                               <div class="popular-post-title">{{  $newuser->full_name }}</div>
                           </div>
                       </a>
                   </li>
               @endforeach
           </ul>
       </div>
       <ins class="adsbygoogle"
           style="display:block"
           data-ad-format="autorelaxed"
           data-ad-client="ca-pub-5437344106154965"
           data-ad-slot="2431624238"></ins>
       <script>
           (adsbygoogle = window.adsbygoogle || []).push({});
       </script>
   </div>
</aside>