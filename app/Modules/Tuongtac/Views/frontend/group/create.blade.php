@extends('frontend.layouts.master')

@section('css')
<!-- Thêm Dropzone CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
<style>
    /* Modern design styles */
    .create-group-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: linear-gradient(to bottom right, #ffffff, #f9fafb);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .page-header {
        text-align: center;
        margin-bottom: 2.5rem;
        position: relative;
    }

    .page-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .page-header:after {
        content: "";
        display: block;
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, #3b82f6, #60a5fa);
        margin: 1rem auto 0;
        border-radius: 4px;
    }

    .alert-info {
        background-color: rgba(224, 242, 254, 0.6);
        border-left: 4px solid #0ea5e9;
        color: #0c4a6e;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
    }

    .alert-info:before {
        content: "💡";
        font-size: 1.5rem;
        margin-right: 1rem;
    }

    .alert-danger {
        background-color: rgba(254, 226, 226, 0.6);
        border-left: 4px solid #ef4444;
        color: #7f1d1d;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.8rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #374151;
        font-size: 0.95rem;
    }

    .required::after {
        content: " *";
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        outline: none;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .help-text {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .form-check {
        display: flex;
        align-items: center;
        padding: 0;
    }

    .form-check-input {
        margin-right: 0.75rem;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .form-check-label {
        font-weight: 500;
        cursor: pointer;
    }

    .dropzone-container {
        margin-top: 0.8rem;
    }

    /* Thiết kế Dropzone */
    .dropzone {
        border: 2px dashed #3b82f6;
        border-radius: 12px;
        background-color: rgba(239, 246, 255, 0.5);
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        min-height: 150px;
        cursor: pointer;
    }

    .dropzone:hover {
        border-color: #2563eb;
        background-color: rgba(239, 246, 255, 0.7);
    }

    .dropzone .dz-message {
        margin: 2em 0;
        font-weight: 500;
        color: #3b82f6;
    }

    .dropzone .dz-preview {
        margin: 10px;
    }

    .dropzone .dz-preview .dz-image {
        border-radius: 8px;
        overflow: hidden;
        width: 120px;
        height: 120px;
        position: relative;
        display: block;
        z-index: 10;
    }

    .dropzone .dz-preview .dz-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dropzone .dz-preview .dz-details {
        margin-top: 8px;
        font-size: 14px;
    }

    .dropzone .dz-preview .dz-progress {
        height: 10px;
        width: 100%;
        background: #eee;
        border-radius: 5px;
        overflow: hidden;
        margin-top: 10px;
    }

    .dropzone .dz-preview .dz-progress .dz-upload {
        background: #3b82f6;
        display: block;
        height: 100%;
        width: 0;
        transition: width 0.3s ease;
    }

    .dropzone .dz-preview .dz-error-message {
        color: #ef4444;
        margin-top: 5px;
        display: none;
    }

    .dropzone .dz-preview.dz-error .dz-error-message {
        display: block;
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

    .submit-btn {
        display: block;
        width: 100%;
        padding: 0.875rem 1.5rem;
        margin-top: 2rem;
        background: linear-gradient(to right, #2563eb, #3b82f6);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
    }

    .submit-btn:hover {
        background: linear-gradient(to right, #1d4ed8, #2563eb);
        transform: translateY(-2px);
        box-shadow: 0 7px 10px rgba(37, 99, 235, 0.3);
    }

    .submit-btn:active {
        transform: translateY(0);
        box-shadow: 0 3px 5px rgba(37, 99, 235, 0.2);
    }

    .text-danger {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .create-group-container {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="create-group-container">
    <div class="page-header">
        <h2>Tạo Nhóm Mới</h2>
    </div>

    <div class="alert alert-info">
        <strong>Lưu ý:</strong> Bạn cần có ít nhất {{ \App\Http\Controllers\Frontend\GroupFrontendController::REQUIRED_POINTS_TO_CREATE_GROUP }} điểm để tạo nhóm. 
        Điểm hiện tại của bạn: <strong>{{ auth()->user()->totalpoint }}</strong>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('group.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="title" class="required">Tên Nhóm</label>
            <input type="text" class="form-control" id="title" name="title" required value="{{ old('title') }}">
            @error('title')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="type_code" class="required">Loại Nhóm</label>
            <select class="form-control" id="type_code" name="type_code" required>
                @foreach($groupTypes as $type)
                    <option value="{{ $type->type_code }}" {{ old('type_code') == $type->type_code ? 'selected' : '' }}>
                        {{ $type->title }}
                    </option>
                @endforeach
            </select>
            @error('type_code')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') }}</textarea>
            <div class="help-text">Mô tả về mục đích, nội quy và hoạt động của nhóm</div>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="" class="form-label required">Ảnh đại diện nhóm</label>
            <div class="dropzone-container">
                <div id="groupImageDropzone" class="dropzone"></div>
                <div id="uploadStatus" class="upload-status"></div>
                <input type="hidden" id="photo" name="photo" value="{{ old('photo') }}"/>
            </div>
            @error('photo')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="is_private" name="is_private" {{ old('is_private') ? 'checked' : '' }}>
            <label class="form-check-label" for="is_private">
                Nhóm riêng tư
            </label>
            <div class="help-text">Nếu chọn, chỉ thành viên được duyệt mới có thể xem nội dung nhóm</div>
        </div>

        <button type="submit" class="submit-btn">Tạo Nhóm</button>
    </form>
</div>
@endsection

@section('scripts')
<!-- Thêm Dropzone JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script>
    // Vô hiệu hóa khả năng tự động tìm kiếm và khởi tạo Dropzone
    Dropzone.autoDiscover = false;
    
    // Theo dõi ảnh đã tải lên
    var uploadedImage = '';
    const uploadStatus = document.getElementById('uploadStatus');
    
    // Khởi tạo Dropzone mới cho phần tử #groupImageDropzone
    var groupImageDropzone = new Dropzone("#groupImageDropzone", {
        url: "{{ route('front.upload.avatar') }}",
        paramName: "photo", // Tên tham số mặc định để gửi file
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: "image/jpeg,image/png,image/gif",
        addRemoveLinks: true,
        dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn ảnh đại diện nhóm",
        dictRemoveFile: "Xóa ảnh",
        dictCancelUpload: "Hủy tải lên",
        thumbnailWidth: 150,
        thumbnailHeight: 150,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        init: function() {
            this.on("addedfile", function(file) {
                // Nếu đã có file trước đó, xóa nó khi thêm file mới
                if (this.files.length > 1) {
                    this.removeFile(this.files[0]);
                }
                uploadStatus.style.display = "none";
            });
            
            this.on("error", function(file, errorMessage) {
                uploadStatus.className = "upload-status error-msg";
                uploadStatus.textContent = "Lỗi tải lên: " + errorMessage;
                uploadStatus.style.display = "block";
            });
            
            this.on("success", function(file) {
                uploadStatus.className = "upload-status success-msg";
                uploadStatus.textContent = "Tải ảnh lên thành công!";
                uploadStatus.style.display = "block";
                setTimeout(() => { uploadStatus.style.display = "none"; }, 3000);
            });
            
            this.on("maxfilesexceeded", function(file) {
                this.removeFile(file);
                alert("Bạn chỉ có thể tải lên 1 ảnh đại diện cho nhóm!");
            });
        },
        success: function(file, response) {
            // Lưu đường dẫn ảnh trả về từ server
            console.log("Phản hồi từ server:", response);
            if (response.status == "true" || response.status === true) {
                uploadedImage = response.url || response.link;
                document.getElementById('photo').value = uploadedImage;
                console.log("Đã lưu đường dẫn ảnh:", uploadedImage);
            }
        },
        removedfile: function(file) {
            // Xóa ảnh và cập nhật input ẩn
            uploadedImage = '';
            document.getElementById('photo').value = '';
            
            // Xóa ảnh khỏi giao diện
            file.previewElement.remove();
        }
    });
</script>
@endsection