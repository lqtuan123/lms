@extends('frontend.layouts.master')
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        h2 {
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 10px 12px;
            width: 100%;
            margin-bottom: 5px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.25);
        }

        .mb-3 {
            margin-bottom: 20px;
        }

        .mt-3 {
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        
        .btn-primary {
            background-color: #4a6cf7;
            border-color: #4a6cf7;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a5bd9;
            border-color: #3a5bd9;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            margin-left: 8px;
            padding: 4px 10px;
            font-size: 0.85rem;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        /* Dropzone styling */
        .dropzone {
            border: 2px dashed #4a6cf7;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        
        .dropzone .dz-message {
            color: #6c757d;
        }
        
        /* TomSelect styling */
        .ts-wrapper {
            margin-bottom: 20px;
        }
        
        .ts-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 6px 12px;
        }
        
        .resource-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .resource-item:last-child {
            border-bottom: none;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <h2>Chỉnh sửa sách</h2>
        <form action="{{ route('user.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Tên sách</label>
                <input type="text" name="title" class="form-control" value="{{ $book->title }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ảnh bìa</label>
                <div class="dropzone" id="imageDropzone" data-url="{{ route('public.upload.avatar') }}"></div>
            </div>
            <!-- Ẩn input để lưu tên file ảnh -->
            <input type="hidden" name="photo" id="uploadedImages" value="{{ $book->photo }}">

            <div class="mb-3">
                <label class="form-label">Thông tin</label>
                <textarea name="summary" class="form-control" rows="4">{{ $book->summary }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Nội dung</label>
                <textarea name="content" class="form-control" rows="8">{{ $book->content }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Tài liệu đính kèm</label>
                <input type="file" name="document[]" class="form-control" multiple>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="replace_documents" id="replaceDocuments" value="1">
                    <label class="form-check-label" for="replaceDocuments">
                        Thay thế tất cả tài liệu hiện tại khi tải lên tài liệu mới
                    </label>
                </div>
                @if ($book->resources)
                    <div class="mt-2">
                        <p>Tài liệu hiện tại:</p>
                        @php
                            if (is_string($book->resources)) {
                                $resources = json_decode($book->resources, true);
                                $resourceIds = $resources['resource_ids'] ?? [];
                            } else {
                                $resourceIds = $book->resources['resource_ids'] ?? [];
                            }
                        @endphp

                        @foreach ($resourceIds as $resourceId)
                            @php
                                $resource = \App\Modules\Resource\Models\Resource::find($resourceId);
                            @endphp
                            @if ($resource)
                                <div class="resource-item">
                                    <span>{{ $resource->file_name }}</span>
                                    <button type="button" class="btn btn-danger btn-sm ms-2 remove-resource"
                                        data-resource-id="{{ $resourceId }}" data-book-id="{{ $book->id }}">
                                        Xóa
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="book_type_id" class="form-label">Loại sách</label>
                <select name="book_type_id" id="book_type_id" class="form-control" required>
                    <option value="">Chọn loại sách</option>
                    @foreach ($bookTypes as $bookType)
                        <option value="{{ $bookType->id }}" {{ $book->book_type_id == $bookType->id ? 'selected' : '' }}>
                            {{ $bookType->title }}
                        </option>
                    @endforeach
                </select>
                @error('book_type_id')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Trạng Thái</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="active" {{ $book->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $book->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="post-form-4" class="form-label">Tags</label>
                <select id="select-junk" name="tag_ids[]" multiple placeholder=" ..." autocomplete="off">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}"
                            {{ in_array($tag->id, $selectedTags ?? []) ? 'selected' : '' }}>
                            {{ $tag->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật sách</button>
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
            
            // Xử lý nút xóa tài liệu
            document.querySelectorAll('.remove-resource').forEach(button => {
                button.addEventListener('click', function() {
                    const resourceId = this.getAttribute('data-resource-id');
                    const bookId = this.getAttribute('data-book-id');
                    const resourceItem = this.closest('.resource-item');
                    
                    // Gửi request AJAX để xóa tài liệu mà không cần confirm
                    fetch(`/user/books/delete-resource/${resourceId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Xóa phần tử khỏi DOM
                            resourceItem.remove();
                            
                            // Thêm thông báo thành công (không sử dụng alert)
                            const successMessage = document.createElement('div');
                            successMessage.className = 'alert alert-success mt-2';
                            successMessage.textContent = 'Đã xóa tài liệu thành công';
                            
                            // Chèn thông báo vào trước danh sách tài liệu
                            const resourceContainer = document.querySelector('.resource-item')?.closest('.mt-2');
                            if (resourceContainer) {
                                resourceContainer.parentNode.insertBefore(successMessage, resourceContainer);
                                
                                // Tự động ẩn thông báo sau 3 giây
                                setTimeout(() => {
                                    successMessage.remove();
                                }, 3000);
                            }
                        } else {
                            // Hiển thị thông báo lỗi
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'alert alert-danger mt-2';
                            errorMessage.textContent = data.message || 'Có lỗi xảy ra khi xóa tài liệu';
                            
                            const resourceContainer = document.querySelector('.resource-item')?.closest('.mt-2');
                            if (resourceContainer) {
                                resourceContainer.parentNode.insertBefore(errorMessage, resourceContainer);
                                
                                setTimeout(() => {
                                    errorMessage.remove();
                                }, 3000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Hiển thị thông báo lỗi không sử dụng alert
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'alert alert-danger mt-2';
                        errorMessage.textContent = 'Có lỗi xảy ra khi xóa tài liệu';
                        
                        const form = document.querySelector('form');
                        form.insertBefore(errorMessage, form.firstChild);
                        
                        setTimeout(() => {
                            errorMessage.remove();
                        }, 3000);
                    });
                });
            });
            
            // Xử lý checkbox thay thế tài liệu
            const replaceCheckbox = document.getElementById('replaceDocuments');
            const resourcesList = document.querySelector('.resource-item')?.closest('.mt-2');
            
            if (replaceCheckbox && resourcesList) {
                replaceCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        resourcesList.style.opacity = '0.5';
                        resourcesList.querySelector('p').textContent = 'Tài liệu hiện tại (sẽ bị xóa khi cập nhật):';
                    } else {
                        resourcesList.style.opacity = '1';
                        resourcesList.querySelector('p').textContent = 'Tài liệu hiện tại:';
                    }
                });
            }
        });
    </script>

    <script>
        // Khởi tạo Dropzone
        Dropzone.autoDiscover = false; // Ngăn Dropzone tự động kích hoạt

        var uploadedImages = []; // Mảng để lưu tên file ảnh đã upload

        // Nếu đã có ảnh, thêm vào mảng uploadedImages
        @if ($book->photo)
            uploadedImages.push("{{ $book->photo }}");
        @endif

        const imageDropzone = new Dropzone("#imageDropzone", {
            url: document.querySelector('#imageDropzone').getAttribute(
            'data-url'), // Get URL from data-url attribute
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
            init: function() {
                // Hiển thị ảnh hiện tại
                if (uploadedImages.length > 0) {
                    let mockFile = {
                        name: "Ảnh hiện tại",
                        size: 0
                    };
                    this.displayExistingFile(mockFile, uploadedImages[0]);
                    this.options.maxFiles = 0; // Không cho phép upload thêm
                }

                this.on("maxfilesexceeded", function(file) {
                    this.removeAllFiles(); // Xóa tất cả file cũ
                    this.addFile(file); // Thêm file mới
                });
            },
            success: function(file, response) {
                // Xóa ảnh cũ khỏi mảng nếu có
                uploadedImages = [];
                // Lưu tên file trả về từ server
                uploadedImages.push(response.link);
                document.getElementById('uploadedImages').value = response.link;
                this.options.maxFiles = 0; // Không cho phép upload thêm
            },
            removedfile: function(file) {
                // Xóa ảnh khỏi giao diện
                file.previewElement.remove();
                // Xóa khỏi mảng và cập nhật input
                uploadedImages = [];
                document.getElementById('uploadedImages').value = '';
                this.options.maxFiles = 1; // Cho phép upload lại
            },
        });
    </script>
@endsection
