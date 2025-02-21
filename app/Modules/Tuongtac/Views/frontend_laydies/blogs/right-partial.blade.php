<?php
use \App\Modules\Tuongtac\Models\TPage;
 $popularPosts = \App\Modules\Tuongtac\Models\TBlog::orderBy('hit','desc')->limit(10) 
        ->get();
$newposts = \App\Modules\Tuongtac\Models\TBlog::orderBy('id','desc')->limit(10) 
        ->get();
$newusers = \App\Models\User::orderBy('id','desc')->limit(5)->get();
?>
<?php
use App\Modules\Group\Models\Group;
 $groups = Group::where('status','active')->paginate(20);
?>
@php
use Illuminate\Support\Str;
@endphp

<aside class="right-menu bottom-right-menu">
    
    <div class="section">
        <div class="popular-posts">
            <nav class="menu">
                <ul>
                    <li><a href="{{route('front.tblogs.index')}}">Tất cả</a></li>
                    <li><a href="{{route('front.tblogs.myblog')}}">Bài viết của tôi</a></li>
                    <li> <a href="{{route('front.tblogs.favblog')}}" >Bài viết quan tâm</a></li>
                    <li><a href="{{route('front.userpages.hornor')}}">Người dùng vinh danh</a></li>
                    <li><a href="{{route('front.userpages.edituser')}}">Thông tin tài khoản</a></li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="section">
        <div class="popular-posts">
            <h3>Nhóm thành viên</h3>
            <ul class="submenu">
                @foreach($groups as $group)
                <li><i class="random-icon">🔥</i> <a href="{{ $group->getPageUrl($group->id)}}" >{{Str::limit($group->title, 20) }} </a></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="section">
        <div class="popular-posts">
            <h3>Có thể bạn quan tâm</h3>
            <ul>
                @foreach($newposts as $post)
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

        <!-- rightbar -->
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-5437344106154965"
            data-ad-slot="1550593306"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
    
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
    
    <div class="section bot-left-menu">
        <div class="popular-posts ">
            <h3>Người dùng mới</h3>
            <ul>
                @foreach($newusers as $newuser)
                
                         
                    <li>
                        <a href=" ">
                            <div class="popular-post-item">
                                  <img src="{{ $newuser->photo}}" alt="{{  $newuser->full_name }}" class="popular-post-thumbnail">
                                <div class="popular-post-title">
                                   <a href="{{TPage::getPageUrl($newuser->id,'user')}}"> {{  $newuser->full_name }} </a>
                                </div>
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