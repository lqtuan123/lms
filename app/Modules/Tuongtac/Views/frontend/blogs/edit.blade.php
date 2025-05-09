@extends('Tuongtac::frontend.blogs.body')
@section('topcss')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">

<!-- Dropzone JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">

<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<style>
    /* Container chính */
    .container {
        margin: 0 auto;
    }

    /* Form controls */
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 10px;
        font-size: 16px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    /* Dropzone styling */
    .dropzone {
        border: 2px dashed #0087F7;
        border-radius: 5px;
        background: white;
        min-height: 150px;
        padding: 20px;
        margin-bottom: 20px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .dropzone .dz-message {
        font-weight: 400;
        font-size: 16px;
        color: #646c9a;
        text-align: center;
        margin: 2em 0;
    }
    
    .dropzone .dz-preview {
        margin: 10px;
    }
    
    .dropzone .dz-preview .dz-error-message {
        font-size: 12px;
    }
    
    .upload-status {
        display: none;
        margin-top: 10px;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
    }
    
    .success-msg {
        background-color: #d4edda;
        color: #155724;
    }
    
    .error-msg {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Buttons */
    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        font-size: 16px;
        text-align: center;
        display: inline-block;
        border: none;
    }

    .btn-primary {
        background-color: #0087F7;
        color: white;
    }

    .btn-secondary {
        background-color: #f8f9fa;
        color: #6c757d;
        border: 1px solid #ddd;
        text-decoration: none;
        padding: 5px 10px;
        font-size: 14px;
        border-radius: 3px;
    }

    .back-button {
        margin-bottom: 20px;
    }

    /* Tags */
    .buttons-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .tag-button {
        background-color: #e9f5ff;
        color: #0087F7;
        border: none;
        padding: 5px 10px;
        border-radius: 15px;
        cursor: pointer;
        font-size: 14px;
    }

    .tag-button:hover {
        background-color: #cce8ff;
    }

    .help-span {
        display: block;
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    
    /* Custom radio buttons */
    .custom-control {
        position: relative;
        padding-left: 25px;
        margin-right: 15px;
        cursor: pointer;
        display: inline-block;
    }

    .custom-control-input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .custom-control-label {
        position: relative;
        cursor: pointer;
        font-size: 16px;
        padding-left: 10px;
    }

    .custom-control-label:before {
        content: '';
        position: absolute;
        left: -15px;
        top: 2px;
        width: 16px;
        height: 16px;
        border: 1px solid #adb5bd;
        border-radius: 50%;
        background-color: white;
    }

    .custom-control-input:checked~.custom-control-label:after {
        content: '';
        position: absolute;
        left: -11px;
        top: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #0087F7;
    }
    
    /* Styles đặc thù cho edit.blade.php */
    .dlt_btn {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #ff5252;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        cursor: pointer;
    }
    
    .product_photo {
        position: relative;
    }
    
    .image-preview {
        display: inline-block;
        position: relative;
        margin: 10px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            margin-bottom: 10px;
        }
    }
    
    /* Styling cho phần tài liệu và URL */
    .document-item {
        border: 1px solid #e2e8f0;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .url-field {
        display: flex;
        align-items: center;
    }
    
    .remove-url, .remove-document {
        background: none;
        border: none;
        color: #e53e3e;
        cursor: pointer;
        font-size: 18px;
        padding: 5px;
    }
    
    .remove-url:hover, .remove-document:hover {
        color: #c53030;
    }
    
    #add-url-field {
        background: none;
        border: none;
        color: #4299e1;
        cursor: pointer;
        font-size: 14px;
        padding: 5px 0;
    }
    
    #add-url-field:hover {
        color: #3182ce;
        text-decoration: underline;
    }
</style>
@endsection
@section('inner-content')
<div >
    <div class="back-button">
        <a href="{{ url()->previous() }}" class="btn-secondary">
            ← Quay lại
        </a>
    </div>
    <h1 class="mb-4">Điều chỉnh bài viết</h1>

    {{-- @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif --}}

    <form action="{{ route('front.tblogs.update',$post->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('patch')
        <!-- Upload ảnh đầu bài -->
        <div class="form-group">
            <label class="form-label">Upload hình ảnh</label>
            <div class="dropzone" id="imageDropzone"></div>
            <div id="uploadStatus" class="upload-status"></div>
        </div>
        <?php
           $images = json_decode($post->photo, true); // Giải mã JSON thành mảng
        ?>
        @if($images)
        <div class="flex ">
            @foreach ( $images as $photo)
            @if($photo!='')
            <div class="image-preview">
                <div class="product_photo">
                    <img class="rounded-md" style="width:50px; height:50px" src="{{$photo}}">
                </div>
                <div title="Xóa hình này?" data-photo="{{$photo}}" class="dlt_btn">x</div>  
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
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="title" class="form-control post-title" value="{{$post->title}}" placeholder="Nhập tiêu đề..." required>
        </div>
        <?php
     
        ?>
        <!-- Thẻ bài viết -->
        <div class="form-group mb-4">
            <label class="form-label">Tags</label>
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
            <label class="form-label">Nội dung bài viết</label>
            <textarea name="content" id="editor" class="form-control" placeholder="Nội dung bài viết ...">{{$post->content}}</textarea>
        </div>
        {{-- chinh sua anh --}}
        <div class="form-group">
            <label for="document" class="form-label">Tài liệu đính kèm</label>
            <input type="file" name="document[]" id='document' class="form-control" multiple>
            
            <!-- Hiển thị tài liệu đã tải lên trước đó -->
            @if (isset($post->documents) && count($post->documents) > 0)
            <div class="mt-3">
                <p class="form-label">Tài liệu hiện tại:</p>
                <div class="document-list">
                    @foreach ($post->documents as $doc)
                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded mb-2 document-item">
                        <div class="flex items-center flex-grow">
                            <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                            <span class="text-sm mr-2">{{ $doc['name'] ?? 'Tài liệu' }}</span>
                            <a href="{{ $doc['url'] }}" target="_blank" class="text-blue-500 hover:text-blue-700 ml-2 text-xs">
                                <i class="fas fa-external-link-alt mr-1"></i>Xem
                            </a>
                        </div>
                        <button type="button" class="text-red-500 hover:text-red-700 remove-document" data-id="{{ $doc['id'] }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
                <!-- Input ẩn để lưu danh sách ID tài liệu cần xóa -->
                <input type="hidden" name="delete_documents" id="delete-documents" value="">
            </div>
            @endif
            
            <div class="mt-3">
                <label class="form-label">URL tài liệu</label>
                <div id="url-fields">
                    @if (isset($post->urls) && count($post->urls) > 0)
                        @foreach ($post->urls as $url)
                        <div class="flex items-center mb-2 url-field">
                            <input type="text" name="urls[]" value="{{ $url }}" class="form-control" style="width: calc(100% - 40px);">
                            <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-url">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    @else
                        <input type="text" name="urls[]" class="form-control" placeholder="URL file (nếu có)">
                    @endif
                </div>
                <button type="button" id="add-url-field" class="mt-2 text-blue-500 hover:text-blue-700 text-sm">
                    <i class="fas fa-plus mr-1"></i> Thêm URL
                </button>
                
                <!-- Input ẩn để lưu danh sách URL cần xóa -->
                <input type="hidden" name="delete_urls" id="delete-urls" value="">
            </div>
        </div>
        <!-- Nút hành động -->
        <div class="form-actions d-flex justify-content-between align-items-center mt-4">
            <button type="submit" class="btn btn-primary">Lưu</button>
          
        </div>
        <input type="hidden" name="frompage" value="{{isset($frompage)?$frompage:''}}"/>
    </form>
           
</div>
@endsection
 
@section('botscript')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let storedUrl = localStorage.getItem('sharedBookUrl');
        if (storedUrl) {
            document.getElementById('book-url').value = storedUrl;
            localStorage.removeItem('sharedBookUrl'); // Xóa sau khi dùng để tránh giữ dữ liệu cũ
        }
        
        // Sử dụng event delegation để xử lý sự kiện click trên nút xóa
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('dlt_btn')) {
                // Lấy phần tử cha và xóa khỏi DOM
                const parent = e.target.closest('.image-preview');
                if (parent) {
                    // Lấy đường dẫn hình ảnh cần xóa
                    const filename = e.target.getAttribute('data-photo');
                    
                    // Xóa khỏi mảng uploadedimages nếu tồn tại
                    if (filename && uploadedimages.includes(filename)) {
                        uploadedimages = uploadedimages.filter(item => item !== filename);
                        
                        // Cập nhật giá trị input hidden
                        document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
                        
                        console.log("Ảnh đã bị xóa:", filename);
                        console.log("Danh sách ảnh hiện tại:", uploadedimages);
                    } else {
                        console.warn("Không tìm thấy ảnh trong mảng:", filename);
                        console.log("Mảng hiện tại:", uploadedimages);
                    }
                    
                    // Xóa phần tử khỏi DOM
                    parent.remove();
                }
            }
        });
    });

    // Khởi tạo Dropzone
    Dropzone.autoDiscover = false; // Ngăn Dropzone tự động kích hoạt

    var uploadedimages = [];
    const uploadStatus = document.getElementById('uploadStatus');
     
    @if($post->photo!= null && $post->photo!='null')
        try {
            // Chuyển đổi từ chuỗi JSON thành mảng JavaScript
            let photoData = @json($images);
            
            // Lọc bỏ các giá trị null, undefined, empty string
            uploadedimages = Array.isArray(photoData) 
                ? photoData.filter(item => item && item !== '') 
                : [];
                
            // In ra console để debug
            console.log("Mảng ảnh khởi tạo:", uploadedimages);
            
            // Cập nhật giá trị input hidden
            document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
        } catch (e) {
            console.error("Lỗi khi phân tích dữ liệu ảnh:", e);
            uploadedimages = [];
            document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
        }
    @else
        uploadedimages = [];
        document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
    @endif
  
  
    const imageDropzone = new Dropzone("#imageDropzone", {
        url: "{{ route('front.upload.avatar') }}", // Route xử lý upload
        paramName: "photo",
        maxFilesize: 5, // Tăng kích thước file tối đa (MB)
        acceptedFiles: 'image/*', // Chỉ chấp nhận file ảnh
        addRemoveLinks: true, // Hiển thị nút xóa ảnh
        dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
        dictRemoveFile: "Xóa ảnh",
        thumbnailWidth: 150, // Chiều rộng tối đa của preview ảnh
        thumbnailHeight: 150, // Chiều cao tối đa của preview ảnh
        maxFiles: 5, // Giới hạn số lượng ảnh
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}" // CSRF Token để bảo vệ form
        },
        init: function() {
            this.on("addedfile", function(file) {
                uploadStatus.style.display = "none";
            });
            
            this.on("error", function(file, errorMessage) {
                uploadStatus.className = "upload-status error-msg";
                uploadStatus.textContent = "Lỗi tải lên: " + (typeof errorMessage === 'string' ? errorMessage : (errorMessage.message || "Lỗi không xác định"));
                uploadStatus.style.display = "block";
                console.error("Lỗi Dropzone:", errorMessage);
            });
            
            this.on("success", function(file, response) {
                uploadStatus.className = "upload-status success-msg";
                uploadStatus.textContent = "Tải lên thành công!";
                uploadStatus.style.display = "block";
                setTimeout(() => { uploadStatus.style.display = "none"; }, 3000);
                
                if (response && response.status && response.link) {
                    // Kiểm tra xem link đã tồn tại trong mảng chưa
                    if (!uploadedimages.includes(response.link)) {
                        uploadedimages.push(response.link);
                        document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
                        console.log("Ảnh đã được thêm vào:", response.link);
                        console.log("Danh sách ảnh hiện tại:", uploadedimages);
                    } else {
                        console.warn("Ảnh đã tồn tại trong danh sách:", response.link);
                    }
                } else {
                    console.error("Phản hồi tải lên không hợp lệ:", response);
                }
            });
            
            this.on("maxfilesexceeded", function(file) {
                this.removeFile(file);
                alert("Bạn chỉ có thể tải lên tối đa 5 ảnh!");
            });
        },
        success: function (file, response) {
            // Xử lý đã được chuyển vào event listener success ở trên
        },
        removedfile: function (file) {
            let filename = '';
            
            // Xác định tên tệp từ phản hồi hoặc từ đối tượng file
            if (file.xhr && file.xhr.response) {
                try {
                    let response = JSON.parse(file.xhr.response);
                    filename = response.link;
                } catch (e) {
                    console.error("Lỗi phân tích phản hồi:", e);
                }
            } else if (file.upload && file.upload.filename) {
                filename = file.upload.filename;
            }
            
            // Xóa tệp khỏi mảng uploadedimages nếu tìm thấy
            if (filename && uploadedimages.includes(filename)) {
                uploadedimages = uploadedimages.filter(item => item !== filename);
                document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
                console.log("Ảnh đã bị xóa từ Dropzone:", filename);
                console.log("Danh sách ảnh hiện tại:", uploadedimages);
            }

            // Xóa ảnh khỏi giao diện
            if (file.previewElement) {
                file.previewElement.remove();
            }
            
            return true;
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
                uploadUrl: '{{route("upload.ckeditor")."?_token=".csrf_token()}}'
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
       allowEmptyOption: false,
       plugins: ['remove_button'], // Thêm plugin xóa button
       persist: false, // Không giữ lại lựa chọn khi trang tải lại
       hideSelected: true, // Ẩn các mục đã chọn khỏi danh sách dropdown
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
    // Thêm kiểm tra trước khi gửi form
    $('form').on('submit', function(e) {
        // Kiểm tra xem trường photo có được thiết lập chính xác không
        let photoValue = document.getElementById('uploadedimages').value;
        console.log("Giá trị trường photo khi gửi form:", photoValue);
        
        try {
            let photos = JSON.parse(photoValue);
            if (!Array.isArray(photos)) {
                document.getElementById('uploadedimages').value = JSON.stringify([]);
            } else if (photos.length === 0) {
                document.getElementById('uploadedimages').value = JSON.stringify([]);
            }
        } catch (e) {
            console.error("Lỗi định dạng JSON trong trường photo:", e);
            document.getElementById('uploadedimages').value = JSON.stringify([]);
        }
    });
</script>
<script>
    // Xử lý thêm trường URL mới
    document.addEventListener('DOMContentLoaded', function() {
        // Biến lưu trữ URL đã xóa
        const deletedUrls = [];

        // Xử lý thêm URL mới
        document.getElementById('add-url-field').addEventListener('click', function() {
            const urlFields = document.getElementById('url-fields');
            const newField = document.createElement('div');
            newField.className = 'flex items-center mb-2 url-field';
            newField.innerHTML = `
                <input type="text" name="urls[]" class="form-control" style="width: calc(100% - 40px);" placeholder="URL file (nếu có)">
                <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-url">
                    <i class="fas fa-times"></i>
                </button>
            `;
            urlFields.appendChild(newField);
        });

        // Xử lý xóa URL bằng event delegation
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.classList.contains('remove-url') || e.target.parentElement.classList.contains('remove-url'))) {
                const button = e.target.classList.contains('remove-url') ? e.target : e.target.parentElement;
                const urlField = button.closest('.url-field');
                
                // Lưu URL đã xóa (để gửi lên server)
                const urlInput = urlField.querySelector('input[name="urls[]"]');
                if (urlInput && urlInput.value) {
                    // Thêm vào mảng các URL đã xóa
                    deletedUrls.push(urlInput.value);
                    
                    // Cập nhật input hidden với mảng JSON các URL đã xóa
                    document.getElementById('delete-urls').value = JSON.stringify(deletedUrls);
                    
                    console.log('URL đã xóa:', deletedUrls);
                    console.log('Giá trị input delete-urls:', document.getElementById('delete-urls').value);
                }
                
                // Xóa trường khỏi DOM
                urlField.remove();
            }
        });

        // Xử lý xóa tài liệu
        const deletedDocuments = [];
        
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.classList.contains('remove-document') || e.target.parentElement.classList.contains('remove-document'))) {
                const button = e.target.classList.contains('remove-document') ? e.target : e.target.parentElement;
                const docItem = button.closest('.document-item');
                
                // Lấy ID tài liệu cần xóa
                const docId = button.getAttribute('data-id');
                
                if (docId) {
                    deletedDocuments.push(docId);
                    document.getElementById('delete-documents').value = JSON.stringify(deletedDocuments);
                    console.log('Tài liệu đã xóa:', deletedDocuments);
                }
                
                // Xóa phần tử khỏi DOM
                docItem.remove();
            }
        });

        // Xử lý form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            // Cập nhật danh sách URL đã xóa vào input hidden
            if (deletedUrls.length > 0) {
                document.getElementById('delete-urls').value = JSON.stringify(deletedUrls);
                console.log('Gửi URLs đã xóa:', deletedUrls);
            }
            
            // Cập nhật danh sách tài liệu đã xóa vào input hidden
            if (deletedDocuments.length > 0) {
                document.getElementById('delete-documents').value = JSON.stringify(deletedDocuments);
                console.log('Gửi tài liệu đã xóa:', deletedDocuments);
            }
        });
    });
</script>
@endsection