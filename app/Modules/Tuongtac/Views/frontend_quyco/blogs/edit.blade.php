@extends('Tuongtac::frontend_quyco.blogs.body')
@section('topcss')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">

<!-- Dropzone JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
@endsection
@section('inner-content')
<div class="back-button">
    <a href="{{ url()->previous() }}" class=" btn-secondary">
        ← Quay lại
    </a>
</div>      
<h4 class="mb-4">Điều chỉnh bài viết </h4>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('front.tblogs.update',$post->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('patch')
       <!-- Upload ảnh đầu bài -->
    <div class="">
        <label>Upload hình ảnh</label>
        <div class="dropzone" id="imageDropzone"></div>
    </div>
    <?php
       $images = json_decode($post->photo, true); // Giải mã JSON thành mảng
    ?>
    @if($images)
    <div class="flex ">
        @foreach ( $images as $photo)
        @if($photo!='')
        <div   style="width:50px; height:50px; margin:10px" class="image-fit cursor-pointer zoom-in">
            <div  class="product_photo">
                <img class="rounded-md "  style="width:50px; height:50px" src="{{$photo}}">
            </div>
            <div title="Xóa hình này?" data-photo="{{$photo}}"   class="dlt_btn">  x</div>  
        </div>
        @endif
        @endforeach
    </div>  
    @endif
    {{-- <input type="hidden" id="photo_old" name="photo_old" value="{{$ad->photo}}"/> --}}
        <!-- Ẩn input để lưu tên file ảnh -->
    <input type="hidden" name="photo" id="uploadedimages" value='{{$post->photo}}'>
    <!-- Tiêu đề bài viết -->
    <div class="form-group mb-4 mt-4">
        <input type="text" name="title" class="form-control post-title" value="{{$post->title}}" placeholder="tiêu đề ..." required>
    </div>
    <?php
 
    ?>
    <!-- Thẻ bài viết -->
    <div class="form-group mb-4">
        <select id="tags" name="tags[]" multiple class="form-control">
            <!-- Nếu có tags trước đó -->
            @foreach ($tags as $tag )
                <option value="{{$tag->id}}" 
                    <?php 
                        foreach($post->tags as $item)
                        {
                            if($item->id == $tag->id)
                                echo 'selected';
                        } 
                    ?>
                >{{$tag->title}}</option>
            @endforeach
        </select>
        <span class="help-span"> Tối đa 5 tag </span>
        <div class="buttons-container">
            @foreach($toptags as $tag)
                <button type="button" class="tag-button" data-tag-id="{{$tag->id}}" data-tag-name="{{$tag->title}}">
                    #{{$tag->title}}
                </button>
                @endforeach
        </div>
    </div>

    <!-- Nội dung bài viết -->
    <div class="form-group mb-4">
        <textarea name="content" id="editor" class="form-control" placeholder="Nội dung bài viết ...">{{$post->content}}</textarea>
    </div>
    {{-- chinh sua anh --}}
    <div class="mt-3">
        <label for="document" class="form-label">Tệp phương tiện (để trống nếu bạn không muốn thay đổi)</label>
        <input type="file" name="document[]" id='document' class="form-control" multiple>
        @if (isset($resources) && count($resources) > 0)
            <div class="mt-3">
                <strong>Tệp đã tải lên:</strong>
                <ul>
                    @foreach ($resources as $resource)
                        <li>
                            <a href="{{ asset($resource->url) }}" target="_blank">
                                {{ $resource->file_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <p class="mt-2 text-gray-600">Chưa có tệp nào được chọn.</p>
        @endif
    </div>
    <!-- Nút hành động -->
    <div class="form-actions d-flex justify-content-between align-items-center">
        <button type="submit" class="btn btn-primary">Lưu</button>
      
    </div>
    <input type="hidden" name="frompage" value="{{isset($frompage)?$frompage:''}}"/>
</form>
           
 
@endsection
 
@section('botscript')
<script>

    
    // Khởi tạo Dropzone
    Dropzone.autoDiscover = false; // Ngăn Dropzone tự động kích hoạt

    var uploadedimages = [];
     
     
    @if($post->photo!= null && $post->photo!='null')
        uploadedimages =    @json($images); // Mảng để lưu tên file ảnh đã upload
    @endif
  
  
    const imageDropzone = new Dropzone("#imageDropzone", {
        url: "{{ route('front.upload.avatar') }}", // Route xử lý upload
        maxFilesize: 2, // Kích thước file tối đa (MB)
        acceptedFiles: 'image/*', // Chỉ chấp nhận file ảnh
        addRemoveLinks: true, // Hiển thị nút xóa ảnh
        dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
        dictRemoveFile: "Xóa ảnh",
        thumbnailWidth: 150, // Chiều rộng tối đa của preview ảnh
        thumbnailHeight: 150, // Chiều cao tối đa của preview ảnh
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}" // CSRF Token để bảo vệ form
        },
        success: function (file, response) {
            // Lưu tên file trả về từ server
            uploadedimages.push(response.link);
            document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages); // Lưu vào input ẩn
        },
        removedfile: function (file) {
            // Xóa file khỏi mảng khi người dùng xóa ảnh
            const filename = file.upload.filename;
            uploadedimages.splice(uploadedimages.indexOf(filename), 1);
            document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages); // Cập nhật input ẩn

            // Xóa ảnh khỏi giao diện
            file.previewElement.remove();
        },
    });
</script>
<script>
  const fileInput = document.getElementById("document");
fileInput.addEventListener("change", function () {
    const files = fileInput.files;
    const maxSize = 20 * 1024 * 1024; // Kích thước tối đa (20 MB)
    const validExtensions = [
        "image/jpeg", "image/png", "image/gif", "image/webp", // Ảnh
        "application/pdf", // PDF
        "application/msword", // DOC
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document", // DOCX
        "application/vnd.ms-powerpoint", // PPT
        "application/vnd.openxmlformats-officedocument.presentationml.presentation", // PPTX
        "application/vnd.ms-excel", // XLS
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", // XLSX
        "application/zip", // ZIP
        "application/x-rar-compressed", // RAR
        "video/quicktime", // MOV
        "text/plain" // TXT
    ];
    
    let validFiles = [];
    
    for (const file of files) {
        if (file.size > maxSize) {
            alert(`File "${file.name}" quá lớn! Kích thước tối đa là 20MB.`);
        } else if (!validExtensions.includes(file.type)) {
            alert(`File "${file.name}" không đúng định dạng cho phép!`);
        } else {
            validFiles.push(file);
        }
    }
    
    if (validFiles.length === 0) {
        fileInput.value = ""; // Xóa tất cả file nếu không hợp lệ
    } else {
        console.log("Các file hợp lệ:", validFiles);
    }
});
</script>
<script src="{{asset('js/js/ckeditor.js')}}"></script>
<script>
     
        // CKSource.Editor
        ClassicEditor.create( document.querySelector( '#editor' ), 
        {
            ckfinder: {
                uploadUrl: '{{route("front.upload.ckeditor")."?_token=".csrf_token()}}'
                }
                ,
                mediaEmbed: {previewsInData: true},
                enterMode: 'BR' ,

        })

        .then( editor => {
            console.log( editor );
        })
        .catch( error => {
            console.error( error );
        })

</script>

<script>
    var tomSelect = new TomSelect("#tags", {
       create: true, // Cho phép tạo mới tags
       maxItems: 5,  // Giới hạn số tags
       placeholder: "Thêm tags",
   });
     // Lấy danh sách nút thẻ
     const tagButtons = document.querySelectorAll('.tag-button');

   // Thêm sự kiện click cho từng nút thẻ
   tagButtons.forEach(button => {
       button.addEventListener('click', function () {
           const tagId = this.getAttribute('data-tag-id');
           const tagName = this.getAttribute('data-tag-name');

           // Thêm tag vào Tom Select
           if (!tomSelect.options[tagId]) {
               tomSelect.addOption({ value: tagId, text: tagName });
           }
           tomSelect.addItem(tagId); // Chọn tag
       });
   });
</script>
<script>
    
    //  uploadedimages = @json($images);
    // document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages); // Cập nhật input ẩn
    // alert(  document.getElementById('uploadedimages').value);
    $(".dlt_btn").click(function(){
        $(this).parent().remove();   
        var link_photo = "";
        
        const filename = $(this).data("photo");
            // alert(filename);
            uploadedimages.splice(uploadedimages.indexOf(filename), 1);
            document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages); // Cập nhật input ẩn

        $('#photo_old').val(link_photo);
    });

     
</script>
@endsection