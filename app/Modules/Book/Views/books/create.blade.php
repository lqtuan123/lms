@extends('backend.layouts.master')

@section ('scriptop')

<meta name="csrf-token" content="{{ csrf_token() }}">
 

<script src="{{asset('js/js/tom-select.complete.min.js')}}"></script>
<link rel="stylesheet" href="{{ asset('/js/css/tom-select.min.css') }}">
@endsection



@section('content')
    <h2 class="intro-y text-lg font-medium mt-10">
        Tạo Sách Mới
    </h2>

    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mt-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Nhập tiêu đề" required>
            @error('title')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3">
            <label for="" class="form-label">Photo</label>
            <div class="px-4 pb-4 mt-5 flex items-center  cursor-pointer relative">
                <div data-single="true" id="mydropzone" class="dropzone  "    url="{{route('admin.upload.avatar')}}" >
                    <div class="fallback"> <input name="file" type="file" /> </div>
                    <div class="dz-message" data-dz-message>
                        <div class=" font-medium">Kéo thả hoặc chọn ảnh.</div>
                            
                    </div>
                </div>
                <input type="hidden" id="photo" name="photo"/>
            </div>
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
            <label for="summary" class="form-label">Tóm Tắt</label>
            <textarea name="summary" id="summary" class="form-control" placeholder="Nhập tóm tắt"></textarea>
        </div>

        <div class="mt-3">
            <label for="content" class="form-label">Nội Dung</label>
            <textarea name="content" id="content" class="form-control" placeholder="Nhập nội dung"></textarea>
        </div>

        <div class="mt-3">
            <label for="document" class="form-label">Tài Liệu</label>
            <input type="file" name="document[]" id="document" class="form-control" multiple required>
            @error('document')
                <div class="text-red-600">{{ $message }}</div>
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

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Tạo Sách</button>
        </div>
    </form>
@endsection

@section ('scripts')

<script>
    var select = new TomSelect('#select-junk',{
        maxItems: null,
        allowEmptyOption: true,
        plugins: ['remove_button'],
        sortField: {
            field: "text",
            direction: "asc"
        },
        onItemAdd:function(){
                this.setTextboxValue('');
                this.refreshOptions();
            },
        create: true
        
    });
    select.clear();
</script>

<script>
 Dropzone.autoDiscover = false;
    
    // Dropzone class:
  
        Dropzone.instances[0].options.multiple = false;
        Dropzone.instances[0].options.autoQueue= true;
        Dropzone.instances[0].options.maxFilesize =  1; // MB
        Dropzone.instances[0].options.maxFiles =1;
        Dropzone.instances[0].options.dictDefaultMessage = 'Drop images anywhere to upload (6 images Max)';
        Dropzone.instances[0].options.acceptedFiles= "image/jpeg,image/png,image/gif";
        Dropzone.instances[0].options.previewTemplate =  '<div class=" d-flex flex-column  position-relative">'
                                        +' <img    data-dz-thumbnail >'
                                        
                                    +' </div>';
        // Dropzone.instances[0].options.previewTemplate =  '<li><figure><img data-dz-thumbnail /><i title="Remove Image" class="icon-trash" data-dz-remove ></i></figure></li>';      
        Dropzone.instances[0].options.addRemoveLinks =  true;
        Dropzone.instances[0].options.headers= {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};

        Dropzone.instances[0].on("addedfile", function (file ) {
        // Example: Handle success event
        console.log('File addedfile successfully!' );
        });
        Dropzone.instances[0].on("success", function (file, response) {
        // Example: Handle success event
        // file.previewElement.innerHTML = "";
        if(response.status == "true")
        $('#photo').val(response.link);
        console.log('File success successfully!' +response.link);
        });
        Dropzone.instances[0].on("removedfile", function (file ) {
        $('#photo').val('');
        console.log('File removed successfully!'  );
        });
        Dropzone.instances[0].on("error", function (file, message) {
        // Example: Handle success event
        file.previewElement.innerHTML = "";
        console.log(file);

        console.log('error !' +message);
        });
        console.log(Dropzone.instances[0].options   );

        // console.log(Dropzone.optionsForElement);

</script>

 
{{-- <script src="{{asset('js/js/ckeditor.js')}}"></script>
<script>
      ClassicEditor
        .create( document.querySelector( '#editor2' ), 
        {
            ckfinder: {
                uploadUrl: '{{route("admin.upload.ckeditor")."?_token=".csrf_token()}}'
                }
                ,
                mediaEmbed: {previewsInData: true}

        })

        .then( editor => {
            console.log( editor );
        })
        .catch( error => {
            console.error( error );
        })

</script> --}}

 
@endsection