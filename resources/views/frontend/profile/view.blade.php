<?php

$setting = \App\Models\SettingDetail::find(1);
$user = auth()->user();

?>

@extends('frontend.layouts.master')
@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
    <style>
        /* Container chính */
        .wrapper {
            padding: 30px 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Form hồ sơ */
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .profile-form {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Các ô nhập liệu */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Avatar */
        .avatar-container {
            text-align: center;
            margin-top: 20px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
            display: block;
            margin: 0 auto 10px;
        }

        /* Dropzone */
        #mydropzone {
            background: #f9f9f9;
            border: 2px dashed #007bff;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            font-size: 14px;
            color: #007bff;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        #mydropzone:hover {
            background: #eef7ff;
        }

        /* Dropzone preview */
        .dz-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }

        .dz-preview img {
            max-width: 100px;
            border-radius: 5px;
        }

        .dz-preview button {
            margin-top: 5px;
        }

        /* Nút cập nhật */
        .btn-submit {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: #218838;
        }

        /* Sidebar */
        .sidebar {
            flex-basis: 250px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }

            .sidebar {
                margin-top: 20px;
            }
        }
    </style>
@endsection

@section('content')
    @include('frontend.layouts.breadcrumb')

    <div class="container">

        <div class="profile-container">
            <div class="profile-form">
                <form method="POST" action="{{ route('front.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="full_name">Tên đầy đủ *</label>
                        <input name="full_name" type="text" value="{{ $profile->full_name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input name="email" type="email" value="{{ $profile->email }}" disabled>
                    </div>
                    <div class="form-group">
                        <label for="phone">Điện thoại *</label>
                        <input name="phone" type="text" value="{{ $profile->phone }}" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Địa chỉ *</label>
                        <input name="address" type="text" value="{{ $profile->address }}" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Mô tả *</label>
                        <input name="description" type="text" value="{{ $profile->description }}" required>
                    </div>

                    <div class="avatar-container">
                        <img class="avatar" id="avatar-preview" src="{{ $profile->photo }}" alt="Avatar">
                        <div id="mydropzone" class="dropzone"></div>
                    </div>

                    <input type="hidden" id="photo" name="photo" value="{{ $profile->photo }}">
                    <input type="submit" class="btn-submit" value="Cập nhật">
                </form>
            </div>

            <aside class="sidebar">
                @include('frontend.layouts.leftaccount')
            </aside>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        Dropzone.autoDiscover = false;

        document.addEventListener("DOMContentLoaded", function() {
            var myDropzone = new Dropzone("#mydropzone", {
                url: "{{ route('front.upload.avatar') }}",
                paramName: "photo",
                maxFilesize: 2,
                maxFiles: 1,
                acceptedFiles: "image/jpeg,image/png,image/gif",
                autoProcessQueue: true,
                addRemoveLinks: true,
                dictDefaultMessage: "Kéo thả ảnh vào đây hoặc click để tải lên",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                previewTemplate: `
            <div class="dz-preview dz-file-preview">
                <img data-dz-thumbnail class="img-thumbnail" style="max-width: 100px;">
                <button type="button" class="btn btn-danger btn-sm mt-2" data-dz-remove>Xóa</button>
            </div>
        `,
                init: function() {
                    var dropzoneInstance = this;

                    // Khi một file mới được thêm vào, xóa file cũ nếu có
                    this.on("addedfile", function(file) {
                        if (this.files.length > 1) {
                            this.removeFile(this.files[0]); // Xóa file cũ nhất
                        }
                    });

                    // Khi file tải lên thành công
                    this.on("success", function(file, response) {
                        if (response.status === true) {
                            document.getElementById("photo").value = response.link;
                            document.getElementById("avatar-preview").src = response.link;
                            console.log("Upload thành công:", response.link);
                        }
                    });

                    // Khi file bị xóa
                    this.on("removedfile", function() {
                        document.getElementById("photo").value = "";
                        document.getElementById("avatar-preview").src =
                            "{{ $profile->photo ?? asset('default-avatar.png') }}";
                        console.log("File removed");
                    });

                    // Khi có lỗi xảy ra
                    this.on("error", function(file, message) {
                        console.error("Lỗi upload:", message);
                    });
                }
            });
        });
    </script>
@endsection
