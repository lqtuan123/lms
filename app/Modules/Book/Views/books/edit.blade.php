@extends('backend.layouts.master')

@section('scriptop')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/js/tom-select.complete.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/js/css/tom-select.min.css') }}">
@endsection

@section('content')
    <h2 class="intro-y text-lg font-medium mt-10">
        Chỉnh sửa sách
    </h2>

    <div class="mt-5">
        <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mt-3">
                <label>Tiêu đề</label>
                <input type="text" name="title" class="form-control" value="{{ $book->title }}" required>
            </div>

            <div class="mt-3">
                <label>Ảnh bìa</label>
                <input type="file" name="photo_file" id="photo_file" class="form-control">
                <input type="hidden" name="photo" id="photo_input" value="{{ $book->photo }}" data-url="{{route('admin.upload.avatar')}}">
                @if ($book->photo)
                    <div class="mt-2">
                        <img src="{{ $book->photo }}" id="photoPreview" style="width: 100px; height: auto; margin-top:10px;">
                    </div>
                @endif
            </div>

            <div class="mt-3">
                <label for="book_type_id" class="form-label">Loại sách</label>
                <select name="book_type_id" id="book_type_id" class="form-control" required>
                    <option value="">Chọn loại sách</option>
                    @foreach ($bookTypes as $bookType)
                        <option value="{{ $bookType->id }}"
                            {{ old('book_type_id', $book->book_type_id) == $bookType->id ? 'selected' : '' }}>
                            {{ $bookType->title }}
                        </option>
                    @endforeach
                </select>
                @error('book_type_id')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-3">
                <label>Tóm tắt</label>
                <textarea name="summary" class="form-control">{{ $book->summary }}</textarea>
            </div>

            <div class="mt-3">
                <label>Nội dung</label>
                <textarea name="content" class="form-control">{{ $book->content }}</textarea>
            </div>

            <div class="mt-3">
                <label for="document" class="form-label">Tệp phương tiện</label>
                <input type="file" name="document[]" class="form-control" multiple>
                @if ($resources && count($resources) > 0)
                    <div class="mt-3">
                        <strong>Tệp đã tải lên:</strong>
                        <ul>
                            @foreach ($resources as $resource)
                                <li>
                                    <a href="{{ asset($resource->url) }}" target="_blank">
                                        {{ $resource->file_name }}
                                    </a>
                                    <a href="javascript:;" class="btn btn-danger btn-sm dltBtn"
                                        data-url="{{ route('admin.books.removeResource', ['bookId' => $book->id, 'resourceId' => $resource->id]) }}"
                                        data-name="{{ $resource->file_name }}">
                                        Xóa
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="mt-2 text-gray-600">Chưa có tệp nào được chọn.</p>
                @endif
            </div>

            <div class="mt-3">
                <label style="min-width:70px" class="form-select-label" for="status">Tình trạng</label>
                <select name="status" class="form-select mt-2 sm:mr-2">
                    <option value="active" {{ $book->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $book->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="mt-3">
                <label for="post-form-4" class="form-label">Tags</label>
                <select id="select-junk" name="tag_ids[]" multiple placeholder=" ..." autocomplete="off">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" {{ in_array($tag->id, $tag_ids) ? 'selected' : '' }}>
                            {{ $tag->title }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
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
       
    </script>

<script>
    // Xử lý tải lên ảnh thông thường
    document.addEventListener('DOMContentLoaded', function() {
        const photoFileInput = document.getElementById('photo_file');
        const photoInput = document.getElementById('photo_input');
        const photoPreview = document.getElementById('photoPreview');
        const previewContainer = photoPreview ? photoPreview.parentElement : null;
        const uploadUrl = photoInput.getAttribute('data-url');
        
        photoFileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('photo', this.files[0]);
                
                // Sử dụng csrf token từ meta tag
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status == "true" || data.link) {
                        // Cập nhật giá trị input hidden
                        photoInput.value = data.link;
                        
                        // Hiển thị ảnh xem trước
                        if (!photoPreview && !previewContainer) {
                            // Tạo phần tử xem trước nếu chưa có
                            const newPreviewContainer = document.createElement('div');
                            newPreviewContainer.className = 'mt-2';
                            
                            const newPhotoPreview = document.createElement('img');
                            newPhotoPreview.id = 'photoPreview';
                            newPhotoPreview.style.width = '100px';
                            newPhotoPreview.style.height = 'auto';
                            newPhotoPreview.style.marginTop = '10px';
                            newPhotoPreview.src = data.link;
                            
                            newPreviewContainer.appendChild(newPhotoPreview);
                            photoFileInput.parentNode.appendChild(newPreviewContainer);
                        } else if (photoPreview) {
                            // Cập nhật ảnh xem trước nếu đã có
                            photoPreview.src = data.link;
                            if (previewContainer) {
                                previewContainer.style.display = 'block';
                            }
                        }
                        
                        console.log('Tải ảnh thành công: ' + data.link);
                    } else {
                        console.error('Lỗi tải ảnh lên');
                        alert('Có lỗi xảy ra khi tải ảnh lên. Vui lòng thử lại.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải ảnh lên. Vui lòng thử lại.');
                });
            }
        });

        // Xử lý nút xóa tài liệu
        const deleteButtons = document.querySelectorAll('.dltBtn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const resourceName = this.getAttribute('data-name');
                
                if (confirm(`Bạn có chắc chắn muốn xóa tài liệu "${resourceName}"?`)) {
                    // Sử dụng csrf token từ meta tag
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Xóa phần tử khỏi DOM
                            this.closest('li').remove();
                            alert('Đã xóa tài liệu thành công');
                        } else {
                            alert(data.message || 'Có lỗi xảy ra khi xóa tài liệu');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi xóa tài liệu');
                    });
                }
            });
        });
    });
</script>

@endsection
