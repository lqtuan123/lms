<div class="mobile-menu md:hidden">
    <div class="mobile-menu-bar">
        <a href="" class="flex mr-auto">
            <img alt="Midone - HTML Admin Template" class="w-6" src="{{asset('backend/images/logo.svg')}}">
        </a>
        <a href="javascript:;" class="mobile-menu-toggler"> <i data-lucide="bar-chart-2" class="w-8 h-8 text-white transform -rotate-90"></i> </a>
    </div>
    <div class="scrollable">
        <a href="javascript:;" class="mobile-menu-toggler"> <i data-lucide="x-circle" class="w-8 h-8 text-white transform -rotate-90"></i> </a>
                
    
<ul>
        <li>
            <a href="{{route('admin.home')}}" class="menu menu{{$active_menu=='dashboard'?'--active':''}}">
                <div class="menu__icon"> <i data-lucide="home"></i> </div>
                <div class="menu__title"> Dashboard </div>
            </a>
        </li> 
       
    
</ul>
    </div>
</div>