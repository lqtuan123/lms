@extends('frontend.layouts.master')
@section('head_css')
<link rel="stylesheet" href="{{asset('frontend/assets_tp/css/custom8.css')}}" type="text/css" />
 <!-- FilePond CSS -->
<!-- Dropzone CSS -->
@yield('topcss')
<style>
 .scroll-to-top {
    position: fixed; /* Cố định vị trí */
    bottom: 20px; /* Cách đáy màn hình 20px */
    right: 20px; /* Cách phải màn hình 20px */
    width: 40px;
    height: 40px;
    background-color: var(--base-color); /* Màu nền */
    color: white; /* Màu chữ */
    border: none;
    border-radius: 50%; /* Bo tròn thành hình tròn */
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Đổ bóng */
    font-size: 20px; /* Kích thước chữ */
    display: none; /* Ẩn mặc định */
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Hiển thị trên cùng */
    transition: background-color 0.3s ease, transform 0.3s ease;
}

/* Hiệu ứng hover */
.scroll-to-top:hover {
    background-color: var(--base-color); /* Màu nền khi hover */
    transform: scale(1.1); /* Phóng to nhẹ */
}
@media screen and (max-width: 768px) {
    .post-tags {
        display: none;
    }
}
.post-tags span {
        display: inline-block;
        white-space: nowrap;
        margin-top:2px;
    }
   
</style>

@endsection
@section('content')
{{-- @include('frontend.layouts.page_title') --}}

<section style="padding-top:0px">
    @include('frontend.layouts.notification')
    <div class="mcontainer dev">
 
           <!-- Left Menu -->
           @include('Tuongtac::frontend.blogs.left-partial')

           <!-- Main Content -->
           <main class="main-content">
               @yield('inner-content')
           </main>
   
           <!-- Right Menu -->
           @include('Tuongtac::frontend.blogs.right-partial')
   
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
   document.addEventListener('DOMContentLoaded', function () {
    const leftSide = document.querySelector('.left-menu');
    const rightSide = document.querySelector('.right-menu');
    const mainContent = document.querySelector('.main-content');

    const syncScroll = () => {
        const mainScrollTop = mainContent.scrollTop;

        // Đồng bộ hóa cuộn left-side
        if (leftSide.scrollHeight - leftSide.scrollTop > leftSide.clientHeight) {
            leftSide.scrollTop = mainScrollTop;
        }

        // Đồng bộ hóa cuộn right-side
        if (rightSide.scrollHeight - rightSide.scrollTop > rightSide.clientHeight) {
            rightSide.scrollTop = mainScrollTop;
        }
    };

    // Lắng nghe sự kiện cuộn từ main-content
    mainContent.addEventListener('scroll', syncScroll);
});
</script>
<script>

    // Lắng nghe sự kiện cuộn
    window.addEventListener('scroll', function () {
        const scrollToTopBtn = document.getElementById('scrollToTopBtn');
        if (window.scrollY > 300) {
            // Hiển thị nút khi cuộn xuống hơn 300px
            scrollToTopBtn.style.display = 'flex';
        } else {
            // Ẩn nút khi ở gần đầu trang
            scrollToTopBtn.style.display = 'none';
        }
    });

    // Hàm cuộn lên đầu trang
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth' // Cuộn mượt mà
        });
    }

    function toggleMenu() {
        const menu = document.querySelector('.left-menu .menu');
        menu.classList.toggle('active');
    }


</script>

{{-- <script src="https://cdn.tiny.cloud/1/sljivccrwgowrmusksk60bxotqp62hwlfuyqsrgh3esuzcz6/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor',
        plugins: 'image code link lists table media preview',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | image media link | code preview',
        image_advtab: true, // Bật chế độ chỉnh sửa ảnh
        height: 500,
        automatic_uploads: true,
        images_upload_url: "{{ route('front.upload.ckeditor') }}", // Endpoint để upload ảnh
        file_picker_types: 'image',
        images_upload_handler: function (blobInfo, success, failure) {
            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            fetch("{{ route('front.upload.ckeditor') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
                .then(response => response.json())
                .then(result => success(result.link)) // Trả về đường dẫn ảnh
                .catch(error => failure(error.message));
        }
    });
</script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const icons = ['📌', '🔥', '✨', '🌟', '🎖️', '💎', '⚡', '💡'];
    const randomIcons = document.querySelectorAll('.random-icon');

    randomIcons.forEach(icon => {
        const randomIndex = Math.floor(Math.random() * icons.length);
        icon.textContent = icons[randomIndex];
    });
});
</script>

<script>
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePopup();
        }
    });
    function openPopup(postId) {
        // Hiển thị popup với nội dung chờ
        const popup = document.getElementById('contentPopup');
        popup.style.display = 'flex';

        // Đặt nội dung chờ
        document.getElementById('popup-title').innerText = 'Đang tải...';
        document.getElementById('popup-body').innerText = 'Vui lòng chờ...';

        // Gửi yêu cầu AJAX để lấy nội dung bài viết
        fetch(`/gettblog/${postId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi khi tải nội dung');
                }
                return response.json();
            })
            .then(data => {
                // Cập nhật nội dung vào popup
                document.getElementById('popup-title').innerText = data.title;
                document.getElementById('popup-body').innerHTML = data.content;
            })
            .catch(error => {
                // Hiển thị lỗi nếu không lấy được nội dung
                document.getElementById('popup-title').innerText = 'Lỗi';
                document.getElementById('popup-body').innerText = 'Không thể tải nội dung bài viết.';
                console.error(error);
            });
    }

    function closePopup() {
        // Đóng popup
        const popup = document.getElementById('contentPopup');
        popup.style.display = 'none';
    }
    

</script>
@endsection