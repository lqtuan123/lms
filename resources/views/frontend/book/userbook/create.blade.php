@extends('frontend.layouts.master')
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .btn{
            margin-top: 10px; 
            border-radius: 5px;
        }

    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <h2>Tạo sách mới</h2>
        <form action="{{ route('front.book.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Tên sách</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="">
                <label>Ảnh bìa</label>
                <div class="dropzone" id="imageDropzone" data-url="{{ route('public.upload.avatar') }}"></div>
            </div>
            <!-- Ẩn input để lưu tên file ảnh -->
            <input type="hidden" name="photo" id="uploadedImages">

            <div class="mb-3">
                <label class="form-label">Thông tin</label>
                <textarea name="summary" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Nội dung</label>
                <textarea name="content" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Tài liệu đính kèm</label>
                <input type="file" name="document[]" class="form-control" multiple required>
            </div>

            <div class="mt-3">
                <label for="book_type_id" class="form-label">Loại sách</label>
                <select name="book_type_id" id="book_type_id" class="form-control" required>
                    <option value="">Chọn loại sách</option>
                    @foreach ($bookTypes as $bookType)
                        <option value="{{ $bookType->id }}" {{ old('book_type_id') == $bookType->id ? 'selected' : '' }}>
                            {{ $bookType->title }}
                        </option>
                    @endforeach
                </select>
                @error('book_type_id')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-3">
                <label for="status" class="form-label">Trạng Thái</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                @error('status')
                    <div class="text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-3">
                <label for="post-form-4" class="form-label">Tags</label>
                <select id="select-junk" name="tag_ids[]" multiple placeholder=" ..." autocomplete="off">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary" >Tạo sách</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var select = new TomSelect('#select-junk', {
                maxItems: null,
                allowEmptyOption: true,
                plugins: ['remove_button'],
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                onItemAdd: function() {
                    this.setTextboxValue('');
                    this.refreshOptions();
                },
                create: true
            });

            // Xóa lựa chọn ban đầu nếu cần
            setTimeout(() => {
                select.clear();
            }, 100);
        });
    </script>

    <script>
        // Khởi tạo Dropzone
        Dropzone.autoDiscover = false; // Ngăn Dropzone tự động kích hoạt

        document.addEventListener("DOMContentLoaded", function() {
            var uploadedImages = []; // Mảng để lưu tên file ảnh đã upload

            try {
                const dropzoneElement = document.querySelector('#imageDropzone');
                const uploadUrl = dropzoneElement.getAttribute('data-url');
                
                if (!uploadUrl) {
                    console.error("Missing upload URL for Dropzone");
                    return;
                }
                
                const imageDropzone = new Dropzone(dropzoneElement, {
                    url: uploadUrl,
                    paramName: "photo",
                    maxFilesize: 2, // Kích thước file tối đa (MB)
                    acceptedFiles: 'image/*', // Chỉ chấp nhận file ảnh
                    addRemoveLinks: true, // Hiển thị nút xóa ảnh
                    dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
                    dictRemoveFile: "Xóa ảnh",
                    thumbnailWidth: 150, // Chiều rộng tối đa của preview ảnh
                    thumbnailHeight: 150, // Chiều cao tối đa của preview ảnh
                    maxFiles: 1, // Chỉ cho phép upload 1 ảnh
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}" // CSRF Token để bảo vệ form
                    },
                    success: function(file, response) {
                        // Lưu tên file trả về từ server
                        uploadedImages = []; // Reset array
                        uploadedImages.push(response.link);
                        document.getElementById('uploadedImages').value = response.link; // Lưu vào input ẩn dưới dạng string
                    },
                    removedfile: function(file) {
                        // Xóa file khỏi mảng khi người dùng xóa ảnh
                        uploadedImages = [];
                        document.getElementById('uploadedImages').value = ''; // Cập nhật input ẩn

                        // Xóa ảnh khỏi giao diện
                        file.previewElement.remove();
                    },
                    error: function(file, errorMessage) {
                        console.error("Dropzone error:", errorMessage);
                        alert("Lỗi tải lên: " + errorMessage);
                    }
                });
            } catch (error) {
                console.error("Error initializing Dropzone:", error);
            }
        });
    </script>
@endsection
