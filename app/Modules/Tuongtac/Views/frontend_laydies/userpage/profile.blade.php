@extends('Tuongtac::frontend_laydies.blogs.body')
@section('inner-content')
<div class="container">

    <div id='s1' class="p-2 post-card ">
        <div class="flex  ">
            <h3 class=" ">
                Thông tin cơ bản
            </h3>
        </div>
        <div >
            <form method="POST" action = "{{route('front.userpages.updateuser')}}">
            @csrf
                <div class="row  ">
                    <div class="col-12  ">
                        <div class="row">
                            <div class="col-12 col-xl-8 col-lg-8 col-md-6 ">
                                <div>
                                    <label for="full_name" class="form-label">Họ tên:</label>
                                    <input id="full_name" name="full_name" type="text" class="form-control" placeholder="" value="{{$profile->full_name}}" required>
                                </div>
                                <div>
                                    <label for="email" class="form-label">Email:</label>
                                    <input id="email" name="email" type="text" class="form-control" placeholder="" value="{{$profile->email}}" required >
                                </div>
                                <div >
                                    <label for="address" class="form-label">Địa chỉ</label>
                                    <input id="address" name="address" class="form-control" placeholder="Địa chỉ"  required value ="{{$profile->address}}" > 
                                </div>
                            
                            </div>
                            <div class="col-12 col-xl-4 col-lg-4 col-md-6 ">
                                
                                <div  >
                                    <label for="phone" class="form-label">Điện thoại</label>
                                    <input id="phone" type="text" name="phone" class="form-control" placeholder="" value="{{$profile->phone}}"  required>
                                </div>
                                <div  >
                                    <label for="username" class="form-label">Username</label>
                                    <input id="username" type="text" name="username" class="form-control" placeholder="" value="{{$profile->username}}"  required>
                                </div>
                                <div  >
                                    <label for="birthday" class="form-label">Ngày sinh (tháng/ngày/năm)</label>
                                    <input id="birthday" type="date" name="birthday" class="form-control" placeholder="" value="{{$profile->birthday}}"  required>
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div >
                                    <label for="description" class="form-label">Mô tả bản thân</label>
                                    <input id="description" name="description" class="form-control" placeholder=""  required value ="{{$profile->description}}" > 
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div style=" padding:15px;" class=" col-12 mx-auto ">
                        
                            <div class="px-4 pb-4 mt-5 flex items-center  cursor-pointer relative">
                                <div data-single="true" id="mydropzone" class="dropzone  "    url="{{route('upload.avatar')}}" >
                                    <div class="fallback"> <input name="file" type="file" /> </div>
                                    <div class="dz-message" data-dz-message>
                                        <div class=" font-medium">Kéo thả hoặc chọn ảnh.</div>
                                            
                                    </div>
                                </div>
                            </div>
                        
                        <input type="hidden" id="photo" name="photo"/>
                        <div class="mx-auto cursor-pointer relative mt-5">
                                Cập nhật ảnh đại diện. Bổ trống nếu bạn không muốn thay đổi.
                                
                        </div>
                    
                    </div>
                </div>
                <button id="btnsubmit" type="submit" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow w-20 mt-3">Lưu</button>
            </form>
        </div>
    </div>
    
    <div id='s2' class="p-2 post-card ">
        <div class="flex  ">
            <h3 class=" ">
                Đổi mật khẩu
            </h3>
        </div>
        <div >
            <form method="POST" action = "{{route('front.userpages.changepassword')}}">
                @csrf
                <div class="   ">
                    <div class="row">
                        <div>
                            <label for="update-profile-form-1" class="form-label">Mật khẩu hiện tại:</label>
                            <input id="update-profile-form-1" name="current_password" type="password" class="form-control" value="" required>
                                
                        </div>
                        <div>
                            <label for="update-profile-form-2" class="form-label">Mật khẩu mới:</label>
                            <input id="update-profile-form-2" name="new_password" type="password" class="form-control"  value="" required >
                            
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Nhập lại mật khẩu mới</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                        </div>
                        <div class="mb-3">
                            <a class=" btn-link" href="{{ route('password.request') }}">
                                {{ __('Quên mật khẩu?') }}
                              </a>
                        </div>
                      
                    </div>
                    <button type="submit" style="padding:10px" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow mx-auto w-20 mt-3 ">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@section('botscript')
<script>
    Dropzone.autoDiscover = false;
       
       // Dropzone class:
       var myDropzone = new Dropzone("div#mydropzone", { url: "{{route('front.upload.avatar')}}"});
           // previewsContainer: ".dropzone-previews",
           // Dropzone.instances[0].options.url = "{{route('upload.avatar')}}";
           Dropzone.instances[0].options.multiple = false;
           Dropzone.instances[0].options.autoQueue= true;
           Dropzone.instances[0].options.maxFilesize =  1; // MB
           Dropzone.instances[0].options.maxFiles =1;
           Dropzone.instances[0].options.dictDefaultMessage = 'Drop images anywhere to upload (6 images Max)';
           Dropzone.instances[0].options.acceptedFiles= "image/jpeg,image/png,image/gif";
           Dropzone.instances[0].options.previewTemplate =  '<div class=" d-flex flex-column  position-relative">'
                                           +' <img class="data-dz-thumbnail" data-dz-thumbnail   >'
                                           
                                       +' </div>';
           // Dropzone.instances[0].options.previewTemplate =  '<li><figure><img data-dz-thumbnail /><i title="Remove Image" class="icon-trash" data-dz-remove ></i></figure></li>';      
           Dropzone.instances[0].options.addRemoveLinks =  true;
           Dropzone.instances[0].options.headers= { 'X-CSRF-TOKEN': "{{ csrf_token() }}" };
           Dropzone.instances[0].options.dictRemoveFile = "xóa";
           Dropzone.instances[0].options.dictCancelUpload = "hủy";
           Dropzone.instances[0].options.dictMaxFilesExceeded = "";
           Dropzone.instances[0].on("maxfilesexceeded", function(file) {
               this.removeFile(file); // Loại bỏ file nếu vượt quá giới hạn
               alert("Tối đa chỉ 1 ảnh"); // Hiển thị thông báo tùy chỉnh
           });
   
           Dropzone.instances[0].on("addedfile", function (file ) {
               document.getElementById("btnsubmit").disabled = true;
           // Example: Handle success event
           console.log('File addedfile successfully!' );
           });
           Dropzone.instances[0].on("success", function (file, response) {
               document.getElementById("btnsubmit").disabled = false;
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
               document.getElementById("btnsubmit").disabled = false;
           // Example: Handle success event
           file.previewElement.innerHTML = "";
           console.log(file);
   
           console.log('error !' +message);
           alert('lỗi: '+message);
           });
           console.log(Dropzone.instances[0].options   );
   
           // console.log(Dropzone.optionsForElement);
    </script>
@endsection