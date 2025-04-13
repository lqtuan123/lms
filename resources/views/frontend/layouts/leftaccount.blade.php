<div class="job-list mb-10">
    <a href="{{route('front.profile')}}" class="card mb-4 lift">
        <div class="card-body p-5">
        
            <span class="flex flex-wrap mx-[-15px] items-center">
            <i style="font-size:200%" class="uil uil-user-nurse"></i> &nbsp;&nbsp;&nbsp;&nbsp; Thông tin tài khoản
            </span>
        </div>
    </a>
    <a href="{{ route('front.book.index') }}?filter=bookmark" class="card mb-4 lift">
        <div class="card-body p-5">
            <span class="flex flex-wrap mx-[-15px]  items-center">
            <i style="font-size:200%" class="uil uil-bookmark"></i>  &nbsp;&nbsp;&nbsp;&nbsp; Danh sách yêu thích
            </span>
        </div>
    </a>
    <a href="{{ route('user.books.index') }}" class="card mb-4 lift">
        <div class="card-body p-5">
            <span class="flex flex-wrap mx-[-15px]  items-center">
            <i style="font-size:200%" class="uil uil-file-alt"></i>  &nbsp;&nbsp;&nbsp;&nbsp; Sách đã đăng
            </span>
        </div>
    </a>
    <a  class="card mb-4 lift">
        <div class="card-body p-5">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="">
                @csrf
                <button class="flex flex-wrap mx-[-15px]  items-center" type="submit">
                    <i style="font-size:200%" class="uil uil-arrow-circle-left"></i>   &nbsp;&nbsp;&nbsp;&nbsp; Đăng xuất
                </button>
            </form>
           
        </div>
    </a>
   
</div>
    