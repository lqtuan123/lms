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

        .btn {
            margin-top: 10px;
            border-radius: 5px;
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

            <div class="">
                <label>Ảnh bìa</label>
                <div class="dropzone" id="imageDropzone" data-url="{{ route('front.upload.avatar') }}"></div>
            </div>
            <!-- Ẩn input để lưu tên file ảnh -->
            <input type="hidden" name="photo" id="uploadedImages" value="{{ $book->photo }}">

            <div class="mb-3">
                <label class="form-label">Thông tin</label>
                <textarea name="summary" class="form-control">{{ $book->summary }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Nội dung</label>
                <textarea name="content" class="form-control">{{ $book->content }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Tài liệu đính kèm</label>
                <input type="file" name="document[]" class="form-control" multiple>
                @if ($book->resources)
                    <div class="mt-2">
                        <p>Tài liệu hiện tại:</p>
                        @php
                            $resourceIds = $book->resources['resource_ids'] ?? [];
                        @endphp

                        @foreach ($resourceIds as $resourceId)
                            @php
                                $resource = \App\Modules\Resource\Models\Resource::find($resourceId);
                            @endphp
                            @if ($resource)
                                <div class="d-flex align-items-center mb-2">
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

            <div class="mt-3">
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

            <div class="mt-3">
                <label for="status" class="form-label">Trạng Thái</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="active" {{ $book->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $book->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <div class="text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-3">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

    <script>
        // Thêm CSRF token vào tất cả các request AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

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

            // Xóa tài liệu
            document.querySelectorAll('.remove-resource').forEach(button => {
                button.addEventListener('click', function() {
                    const resourceId = this.dataset.resourceId;
                    const resourceElement = this.parentElement;

                    Swal.fire({
                        title: 'Bạn có chắc muốn xóa tài liệu này?',
                        text: "Bạn không thể khôi phục lại sau khi xóa!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Vâng, xóa nó!',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/user/books/resource/${resourceId}`,
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        resourceElement.remove();
                                        Swal.fire(
                                            'Đã xóa!',
                                            response.message,
                                            'success'
                                        );
                                    } else {
                                        Swal.fire(
                                            'Lỗi!',
                                            response.message ||
                                            'Có lỗi xảy ra khi xóa tài liệu',
                                            'error'
                                        );
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Error:', xhr);
                                    let errorMessage =
                                        'Có lỗi xảy ra khi xóa tài liệu';
                                    if (xhr.responseJSON && xhr.responseJSON
                                        .message) {
                                        errorMessage = xhr.responseJSON.message;
                                    }
                                    Swal.fire(
                                        'Lỗi!',
                                        errorMessage,
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });
            });
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
