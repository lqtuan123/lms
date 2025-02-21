
<?php
use App\Modules\Group\Models\Group;
 $groups = Group::where('status','active')->paginate(20);
?>
<aside class="left-menu">
    <button class="menu-toggle" onclick="toggleMenu()">☰</button> <!-- Hamburger button -->
     
        <nav class="menu">
            <ul>
                <li><a href="{{route('front.userpages.edituser')}}">Nhóm thành viên</a></li>
                <ul class="submenu">
                    @foreach($groups as $group)
                    <li><i class="random-icon">🔥</i> <a href="{{ $group->getPageUrl($group->id)}}" >{{Str::limit($group->title, 20) }} </a></li>
                     @endforeach
                </ul>
                <li><a href="{{route('front.tblogs.myblog')}}">Bài viết của tôi</a></li>
                <li> <a href="{{route('front.tblogs.favblog')}}" >Bài viết quan tâm</a></li>
                <li><a href="{{route('front.userpages.hornor')}}">Người dùng vinh danh</a></li>
                <li><a href="{{route('front.userpages.edituser')}}">Thông tin tài khoản</a></li>
              
            </ul>
        </nav>
    

</aside>
