@extends('backend.layouts.master')
@section ('scriptop')

<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>

@endsection
@section('content')
<div>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Thêm group
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <!-- BEGIN: Form Layout -->
            <form method="post" action="{{ route('admin.group.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="intro-y box p-5">
                    {{-- Error Handling --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
            
                    {{-- Title Input --}}
                    <div class="mt-3">
                        <label for="title" class="form-label">Title</label>
                        <input id="title" name="title" type="text" class="form-control" placeholder="Enter title" value="{{ old('title') }}">
                        @error('title')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            
                    
            
                    {{-- Photo Upload --}}
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
            
                    
            
                    {{-- Description Input --}}
                    <div class="mt-3">
                        <label for="description" class="form-label">Giới thiệu</label>
                        <textarea id="editor1" name="description" class="form-control" placeholder="nội dung mô tả nhóm">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            
                    {{-- Is Private Checkbox --}}
                    <div class="mt-3">
                        <label for="is_private" class="form-label">Riêng tư?</label>
                        <input id="is_private" name="is_private" type="checkbox" value="1" {{ old('is_private') ? 'checked' : '' }}>
                        @error('is_private')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            
                    {{-- Status Select --}}
                    <div class="mt-3">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select id="status" name="status" class="form-select mt-2">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Type Code Dropdown --}}
                    <div class="mt-3">
                        <label for="type_code" class="form-label">Loại nhóm</label>
                        <select id="type_code" name="type_code" class="form-select mt-2">
                           
                            @foreach($groupTypes as $groupType)
                                <option value="{{ $groupType->type_code }}" {{ old('type_code') == $groupType->type_code ? 'selected' : '' }}>
                                    {{ $groupType->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_code')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="text-right mt-5">
                        <button type="submit" class="btn btn-primary w-24">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section ('scripts')

 
<script src="{{asset('js/js/ckeditor.js')}}"></script>
<script>
     
        // CKSource.Editor
        ClassicEditor.create( document.querySelector( '#editor1' ), 
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

</script>
<script>
    Dropzone.autoDiscover = false;
       
        Dropzone.instances[0].options.url = "{{route('admin.upload.avatar')}}";
           Dropzone.instances[0].options.multiple = false;
           Dropzone.instances[0].options.autoQueue= true;
           Dropzone.instances[0].options.maxFilesize =  1; // MB
           Dropzone.instances[0].options.maxFiles =1;
           Dropzone.instances[0].options.dictDefaultMessage = 'Kéo thả 1 ảnh';
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
@endsection