@extends('backend.layouts.master')

@section('scriptop')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/js/tom-select.complete.min.js')}}"></script>
<link rel="stylesheet" href="{{ asset('/js/css/tom-select.min.css') }}">
@endsection

@section('content')
    <h2 class="intro-y text-lg font-medium mt-10">
        Tạo bài viết mới
    </h2>

    <form action="{{ route('admin.tblogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mt-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Nhập tiêu đề" required>
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
                <input type="hidden" id="photo" name="photo"/>
            </div>
        </div>

        <div class="mt-3">
            <label for="content" class="form-label">Nội dung</label>
            <textarea name="content" id="editor" class="form-control" placeholder="Nhập nội dung" required></textarea>
            @error('content')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <label for="document" class="form-label">Tài liệu đính kèm</label>
            <input type="file" name="document[]" id="document" class="form-control" multiple>
            @error('document')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <label for="urls" class="form-label">URL tài liệu</label>
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
                <option value="1">Hiển thị</option>
                <option value="0">Ẩn</option>
            </select>
            @error('status')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <label for="tags" class="form-label">Tags</label>
            <select id="select-tags" name="tags[]" multiple placeholder="Chọn hoặc tạo tags mới..." autocomplete="off">
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Tạo bài viết</button>
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
    select.clear();

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
        file.previewElement.classList.add("dz-error");
        file.previewElement.querySelector(".dz-error-message").textContent = message;
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