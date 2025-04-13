@extends('backend.layouts.master')

@section('scriptop')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/js/tom-select.complete.min.js')}}"></script>
<link rel="stylesheet" href="{{ asset('/js/css/tom-select.min.css') }}">
@endsection

@section('content')
    <h2 class="intro-y text-lg font-medium mt-10">
        Chỉnh sửa bài viết
    </h2>

    <form action="{{ route('admin.tblogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mt-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ $blog->title }}" required>
            @error('title')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <label for="" class="form-label">Hình ảnh</label>
            <div class="px-4 pb-4 mt-5 flex items-center cursor-pointer relative">
                <div data-single="true" id="mydropzone" class="dropzone" url="{{route('admin.upload.avatar')}}">
                    <div class="fallback"><input name="file" type="file" /></div>
                    <div class="dz-message" data-dz-message>
                        <div class="font-medium">Kéo thả hoặc chọn ảnh.</div>
                    </div>
                </div>
                <input type="hidden" id="photo" name="photo" value="{{ $blog->photo }}"/>
            </div>
            @if($blog->photo)
                <div class="mt-2">
                    <p>Ảnh hiện tại:</p>
                    @php
                        $photos = is_array($blog->photo) ? $blog->photo : json_decode($blog->photo, true);
                        $photoUrl = is_array($photos) && count($photos) > 0 ? $photos[0] : 
                                    (is_string($blog->photo) && $blog->photo != '' ? $blog->photo : null);
                    @endphp
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $blog->title }}" style="max-width: 200px; max-height: 200px;">
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-3">
            <label for="content" class="form-label">Nội dung</label>
            <textarea name="content" id="editor" class="form-control" required>{{ $blog->content }}</textarea>
            @error('content')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <label for="document" class="form-label">Tài liệu đính kèm mới</label>
            <input type="file" name="document[]" id="document" class="form-control" multiple>
            @error('document')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        @if(isset($resources) && count($resources) > 0)
            <div class="mt-3">
                <label class="form-label">Tài liệu hiện tại</label>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Loại</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resources as $resource)
                                <tr>
                                    <td>
                                        @if(Str::contains($resource->url, ['jpg', 'jpeg', 'png', 'gif']))
                                            <img src="{{ asset($resource->url) }}" alt="{{ $resource->file_name }}" style="max-width: 50px; max-height: 50px;">
                                        @endif
                                        {{ $resource->file_name ?? 'N/A' }}
                                    </td>
                                    <td>{{ $resource->type_code ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ asset($resource->url) }}" target="_blank" class="btn btn-sm btn-primary">Xem</a>
                                        <form action="{{ route('admin.tblogs.resource.destroy', ['blogId' => $blog->id, 'resourceId' => $resource->id]) }}" 
                                              method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn có chắc muốn xóa tài liệu này?')">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mt-3">
            <label for="urls" class="form-label">Thêm URL tài liệu</label>
            <div id="url-container">
                <div class="input-group mb-2">
                    <input type="text" name="urls[]" class="form-control" placeholder="Nhập URL tài liệu">
                    <button type="button" class="btn btn-outline-secondary add-url-btn">+</button>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select name="status" id="status" class="form-control" required>
                <option value="1" {{ $blog->status == 1 ? 'selected' : '' }}>Hiển thị</option>
                <option value="0" {{ $blog->status == 0 ? 'selected' : '' }}>Ẩn</option>
            </select>
            @error('status')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <label for="tags" class="form-label">Tags</label>
            <select id="select-tags" name="tags[]" multiple placeholder="Chọn hoặc tạo tags mới..." autocomplete="off">
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" {{ in_array($tag->id, $tag_ids) ? 'selected' : '' }}>
                        {{ $tag->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Cập nhật bài viết</button>
        </div>
    </form>
@endsection

@section('scripts')
<script src="{{asset('js/js/ckeditor.js')}}"></script>
<script>
    var select = new TomSelect('#select-tags', {
        maxItems: 5,
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

    // Select pre-selected tags
    @if(isset($tag_ids) && count($tag_ids) > 0)
        @foreach($tag_ids as $tag_id)
            select.addItem("{{ $tag_id }}");
        @endforeach
    @endif

    // CKEditor initialization
    ClassicEditor.create(document.querySelector('#editor'), {
        ckfinder: {
            uploadUrl: '{{route("admin.upload.ckeditor")."?_token=".csrf_token()}}'
        },
        mediaEmbed: {
            previewsInData: true
        }
    })
    .then(editor => {
        console.log('CKEditor initialized successfully');
    })
    .catch(error => {
        console.error('CKEditor initialization error:', error);
    });

    // Add URL field functionality
    document.querySelector('.add-url-btn').addEventListener('click', function() {
        const container = document.getElementById('url-container');
        const newGroup = document.createElement('div');
        newGroup.className = 'input-group mb-2';
        newGroup.innerHTML = `
            <input type="text" name="urls[]" class="form-control" placeholder="Nhập URL tài liệu">
            <button type="button" class="btn btn-outline-secondary remove-url-btn">-</button>
        `;
        container.appendChild(newGroup);

        // Add event listener to the new remove button
        newGroup.querySelector('.remove-url-btn').addEventListener('click', function() {
            container.removeChild(newGroup);
        });
    });

    // Dropzone initialization
    Dropzone.autoDiscover = false;
    
    var myDropzone = new Dropzone("#mydropzone", {
        url: "{{route('admin.upload.avatar')}}",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: "image/jpeg,image/png,image/gif,image/webp",
        autoQueue: true,
        addRemoveLinks: true,
        dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
        dictRemoveFile: "Xóa ảnh",
    });

    // Add existing image to Dropzone if it exists
    @if($blog->photo)
        @php
            $photoUrl = '';
            if(is_array($blog->photo)) {
                $photoUrl = count($blog->photo) > 0 ? $blog->photo[0] : '';
            } elseif(is_string($blog->photo) && $blog->photo != '') {
                $photoUrl = $blog->photo;
            }
        @endphp
        @if($photoUrl)
            // Create a mock file representing the existing image
            var mockFile = { name: "{{ basename($photoUrl) }}", size: 12345 };
            myDropzone.displayExistingFile(mockFile, "{{ $photoUrl }}");
        @endif
    @endif

    myDropzone.on("success", function(file, response) {
        if(response.status) {
            document.getElementById('photo').value = response.link;
            console.log('File uploaded successfully:', response.link);
        }
    });

    myDropzone.on("removedfile", function() {
        document.getElementById('photo').value = '';
        console.log('File removed');
    });

    myDropzone.on("error", function(file, message) {
        console.error('Upload error:', message);
        if (file.previewElement) {
            file.previewElement.classList.add("dz-error");
            if (typeof message !== "string" && message.error) {
                message = message.error;
            }
            for (var node of file.previewElement.querySelectorAll("[data-dz-errormessage]")) {
                node.textContent = message;
            }
        }
    });

    // Validate file size for document uploads
    document.getElementById('document').addEventListener('change', function() {
        const files = this.files;
        const maxSize = 20 * 1024 * 1024; // 20MB
        
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
                alert(`File "${files[i].name}" vượt quá kích thước cho phép (20MB).`);
                this.value = ''; // Clear the input
                break;
            }
        }
    });
</script>
@endsection 