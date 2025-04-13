@extends('frontend.layouts.master1')
@section('css')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/custom8.css') }}" type="text/css" />
<style>
.image-container {
    position: relative;
    display: inline-block;
}

.banner, .logo {
    width: 200px;
    height: 200px;
    object-fit: cover;
}

/* Định dạng chung cho icon chỉnh sửa */
.edit-icon, .edit-icon1 {
    position: absolute;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    padding: 5px;
    border-radius: 50%;
    cursor: pointer;
    display: block;
}

.edit-icon {
    top: 10px;
    right: 10px;
}

.edit-icon1 {
    margin: 10px 0 0 10px ;
    left:48%;
}

.image-container:hover .edit-icon {
    display: block;
}

/* Popup chỉnh sửa */
.popup, .edit-form {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    z-index: 1000;
}

.popup {
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

.popup-content {
    position: relative;
    width: 400px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
    left:40%;
    top:10%;
}

/* Hiệu ứng khi hover vào poll card */
.poll-card:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}

.avatar {
    border-radius: 50%;
    width: 100px;
    height: 100px;
}

</style>
@endsection
@section('content')
 
    <section class="  top-space-margin page-title-big-typography cover-background overflow-hidden position-relative p-0 border-radius-10px lg-no-border-radius" style="background-image: url({{isset($page->banner)?$page->banner:'https://via.placeholder.com/1920x600'}})"> 
        <div class=" h-300px container">
                    &nbsp;
        </div>
        @if($is_admin)
        <div class="edit-icon" onclick="openEditPopup('banner')">
            ✏️
        </div>
        @endif
    </section>
        <!-- end section -->
        <!-- start section -->
    <section class="overlap-section text-center p-0 sm-pt-50px">
        @if($is_admin)
        <div class="edit-edit">
            <div class="edit-icon1" onclick="openEditPopup('logo')">
                ✏️
            </div>
        </div>
        
        @endif
        <img   class="avatar   box-shadow-medium-bottom w-150px bg-white  border border-9 border-color-white" src="{{ $page->avatar}}" title ="{{$page->title }} " alt="{{$page->title}}">
        
        <h1 style="font-size:200% !important; text-transform: uppercase;" class="text-dark-gray fw-700"> {{$page->title}} </h1>
        
    </section>
     
      {{-- <div class="image-container">
        <img src="{{$page->banner}}" alt="Banner" class="banner">
        <div class="edit-icon" onclick="openEditForm('banner')">
            ✏️
        </div>
    </div>
    
    <div class="image-container">
        <img src="{{$page->photo}}" alt="Logo" class="logo">
        <div class="edit-icon" onclick="openEditForm('logo')">
            ✏️
        </div>
    </div> --}}
    
<!-- Popup cập nhật hình ảnh -->
<div id="edit-popup" class="popup" onclick="closePopupOnBackgroundClick(event)" >
    <div class="popup-content">
        <span class="close-btn" onclick="closeEditPopup()">&times;</span>
        <form id="updateForm" enctype="multipart/form-data" method="POST" action="{{ route('front.tpage.updateimage') }}">
            @csrf
            <input type="hidden" name="page_id" id="page_id" value="{{$page->id}}">
            <input type="hidden" name="type" id="type">
            <label for="image">CHỌN ẢNH</label>
            <input type="file" name="image" id="image" accept="image/*" style="margin:5px" required>
            <button type="submit" class="  "  style="margin:5px">Cập nhật</button>
        </form>
    </div>
</div>
<section style="padding-top:0px">
    @include('frontend.layouts.notification')
    <div class="mcontainer dev">
 
           <!-- Left Menu -->
           @include('Tuongtac::frontend.pages.left')

           <!-- Main Content -->
           <main class="main-content">
               @yield('inner-content')
           </main>
   
           <!-- Right Menu -->
           @include('Tuongtac::frontend.pages.right')
   
    </div>
    <div id="spinner" style="display: none;">
        <div class="spinner"></div>
    </div>
    <button id="scrollToTopBtn" class="scroll-to-top" onclick="scrollToTop()">▲</button>
</section>
@endsection

@section('footscripts')
@yield('botscript')
 <script>
  // Mở popup
    function openEditPopup(type) {
        const popup = document.getElementById('edit-popup');
        const typeInput = document.getElementById('type');
        typeInput.value = type; // Gán loại hình ảnh (banner hoặc logo)
        popup.style.display = 'block';

        // Thêm event listener để bắt phím Esc
        document.addEventListener('keydown', handleEscKey);
    }

    // Đóng popup
    function closeEditPopup() {
        const popup = document.getElementById('edit-popup');
        popup.style.display = 'none';

        // Xóa event listener khi popup đóng
        document.removeEventListener('keydown', handleEscKey);
    }

    // Đóng popup khi bấm vào nền đen
    function closePopupOnBackgroundClick(event) {
        const popupContent = document.querySelector('.popup-content');
        
        if (!popupContent.contains(event.target)) {
            closeEditPopup();
        }
    }

    // Đóng popup khi nhấn phím Esc
    function handleEscKey(event) {
        if (event.key === 'Escape') {
            closeEditPopup();
        }
    }

 </script>
@endsection