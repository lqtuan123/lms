@extends('frontend.layouts.master')
@section('css')
    <style>
        body {
            /* background-color: #fdfaf5; */
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .flex {
            display: flex;
            flex-wrap: wrap;
        }

        .left-section {
            width: 30%;
            padding: 15px;
        }

        .right-section {
            width: 70%;
            padding: 15px;
        }

        .box {
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .book-image {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .book-title {
            font-size: 26px;
            font-weight: bold;
            color: #333;
        }

        .book-info label {
            font-weight: bold;
            color: #555;
        }

        .button-group {
            margin-top: 15px;
        }

        .button-group button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-primary {
            background: #d9534f;
            color: white;
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }

        .content-section {
            margin-top: 20px;
        }

        .content-section h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .tag-group span {
            display: inline-block;
            background: #e6f4ea;
            color: #217346;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 5px 2px;
            font-size: 14px;
        }

        .comment {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #d9534f;
            transition: all 0.3s ease;
        }

        .comment:hover {
            background: #f1f1f1;
        }

        .comment p {
            margin: 0;
        }

        .comment small {
            color: #777;
            font-size: 12px;
        }

        .comment-form {
            margin-top: 15px;
        }

        .comment-form textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
            font-size: 14px;
        }

        .comment-form textarea:focus {
            border-color: #d9534f;
            outline: none;
            box-shadow: 0 0 5px rgba(217, 83, 79, 0.5);
        }

        #submit-comment {
            background: #d9534f;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        #submit-comment:hover {
            background: #c9302c;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            background-color: #6c757d;
            /* Màu xám nhạt */
            color: #fff;
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-button i {
            margin-right: 5px;
        }

        .back-button:hover {
            background-color: #5a6268;
            /* Tối hơn khi hover */
        }
    </style>
@endsection

@section('content')

    <div class="container">
        <div class="flex">
            <div class="left-section">
                <div class="box">
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                    <?php $photos = explode(',', $book->photo); ?>
                    @foreach ($photos as $photo)
                        <img src="{{ $photo }}" class="book-image" />
                    @endforeach
                </div>
            </div>
            <div class="right-section">
                <div class="box">
                    <h2 class="book-title">{{ $book->title }}</h2>
                    <div class="book-info">
                        <p><label>Tác giả:</label> {{ $book->user->full_name }}</p>
                        <p><label>Loại sách:</label> {{ $book->bookType->title }}</p>
                        <p><label>Tag:</label></p>
                        <div class="tag-group">
                            @foreach ($tagNames as $tag)
                                <span>{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="button-group">
                        <button class="btn-primary">Đọc Sách</button>
                        <button class="btn-secondary">Đánh Dấu</button>
                        <button class="btn-secondary">Đánh Giá</button>
                        <button class="btn-secondary">Thảo Luận</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-section box">
            <h3>Tóm tắt</h3>
            <p>{!! nl2br(e($book->summary)) !!}</p>
        </div>

        <div class="content-section box">
            <h3>Nội dung</h3>
            <p>{!! nl2br(e($book->content)) !!}</p>
        </div>

        <div class="content-section box">
            <h3>Tài nguyên</h3>
            @if ($resources->count() > 0)
                @foreach ($resources as $resource)
                    @if ($resource->file_type == 'video/mp4')
                        <video controls class="w-full h-auto my-2">
                            <source src="{{ asset($resource->url) }}" type="video/mp4">
                        </video>
                    @elseif (in_array($resource->file_type, ['image/jpeg', 'image/png', 'image/gif']))
                        <img src="{{ asset($resource->url) }}" class="book-image my-2">
                    @elseif ($resource->file_type == 'audio/mp3')
                        <audio controls class="w-full my-2">
                            <source src="{{ asset($resource->url) }}" type="audio/mp3">
                        </audio>
                    @elseif ($resource->file_type == 'application/pdf')
                        <iframe src="{{ asset($resource->url) }}" class="w-full h-[500px] my-2 border rounded-lg"></iframe>
                        <p><a href="{{ asset($resource->url) }}" target="_blank" class="text-blue-500 underline">Tải xuống
                                PDF</a></p>
                    @else
                        <p>Không hỗ trợ định dạng tệp này.</p>
                    @endif
                @endforeach
            @else
                <p>Không có tài nguyên nào.</p>
            @endif
        </div>

        <div class="content-section box" id="discussion-section">
            <div class="book-comments">
                <div id="comment-section">
                    {!! $comments !!}
                </div>
            </div>

        </div>
    </div>

@endsection
