<?php
use Illuminate\Support\Str;
$toptags = \App\Modules\Tuongtac\Models\TTag::where('id','<=',5)->orderBy('hit','desc')->get();
$ids = array();
foreach($toptags as $tag)
    $ids[] = $tag->id;
// Query the tags based on the collected IDs
$menutags = \App\Modules\Tuongtac\Models\TTag::where('id', '>', 5)
    ->whereNotIn('id', $ids)
    ->orderBy('hit', 'desc')
    ->limit(10)
    ->get();

    $tags = \App\Modules\Tuongtac\Models\TTag::where('id', '>', 5)
    ->whereNotIn('id', $ids)
    ->orderBy('hit', 'desc')
    ->skip(10)
    ->limit(50)
    ->get();
?>
<?php
use App\Modules\Group\Models\Group;
 $groups = Group::where('status','active')->orderBy('id','desc')->limit(5)->get();
?>
<aside class="left-menu">
    <button class="menu-toggle" onclick="toggleMenu()">☰</button> <!-- Hamburger button -->
    <nav style="margin-top:5px" class="menu">
       
        <ul>  
           
            <li class='left-hidden-ul'><a href="{{route('front.tblogs.index')}}">Tất cả</a> 
                <ul  class="submenu">
                    <li><a href="{{route('front.tblogs.myblog')}}">Bài viết của tôi</a></li>
                    <li> <a href="{{route('front.tblogs.favblog')}}" >Bài viết quan tâm</a></li>
                    <li><a href="{{route('front.userpages.edituser')}}">Thông tin tài khoản</a></li>
                </ul>
            </li>
             <li class='left-hidden-ul'>
                <a href="{{route('front.userpages.edituser')}}">Nhóm thành viên</a>
                <ul class="submenu">
                    @foreach($groups as $group)
                    <li><i class="random-icon">🔥</i> <a href="{{ $group->getPageUrl($group->id)}}" >{{Str::limit($group->title, 20) }} </a></li>
                    @endforeach
                </ul>
            </li>
            <li><a href="{{route('front.tblogs.index')}}">Danh mục</a> 
                <ul class="submenu">
                    @foreach($toptags as $tag)
                    <li><i class="random-icon">🔥</i> <a href="{{route('front.tblogs.tag',$tag->slug)}}" >{{Str::limit($tag->title, 20) }} </a></li>
                     @endforeach
                    @foreach($menutags as $tag)
                    <li><i class="random-icon">🔥</i> <a href="{{route('front.tblogs.tag',$tag->slug)}}" >{{Str::limit($tag->title, 20) }} </a></li>
                     @endforeach
                </ul>
            </li>
          
        </ul>
    </nav>
    <div class="section bot-left-menu">
        <div class="post-tags">
           
            @foreach($tags  as $tag)
               <a href="{{route('front.tblogs.tag',$tag->slug)}}"> <span>#{{$tag->title}}</span>   </a>
            @endforeach
        </div>
    </div>

</aside>
