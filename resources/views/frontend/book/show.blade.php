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
            height: 350px;
            padding: 15px 15px 15px 0;
        }

        .right-section {
            width: 70%;
            height: 350px;
            padding: 15px;
            padding: 15px 0 15px 15px;
        }

        .box {
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;

        }

        .box-image {
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            height: 100%;
            text-align: center;
        }

        .box-content {
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .book-image {
            height: 100%;
            border-radius: 5px;
            max-width: 100%;
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

        .back {
            width: 100%;
        }

        .button-group {
            margin-top: 15px;
        }

        .button-group a {
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

        .no-wrap {
            white-space: nowrap;
            /* Ngăn không cho xuống dòng */
            overflow: hidden;
            /* Ẩn nội dung vượt quá */
            text-overflow: ellipsis;
            /* Thêm dấu "..." nếu bị cắt */
        }


        .comment {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            /* border-left: 4px solid #d9534f; */
            transition: all 0.3s ease;
        }

        .comment:hover {
            /* background: #fcf8f8; */
            border-left: 4px solid #d9534f;
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

        /* vote */
        .rating-container {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .rating-container .star {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s;
        }

        .rating-container .star.selected {
            color: #ffcc00;
        }

        .rating-container .star:hover {
            color: #ffcc00;
        }

        /* Highlight stars on hover */
        .rating-container .star.hover {
            color: #ffcc00;
        }

        .postprivate {
            border: 1px dashed black !important;
        }

        /* spinner */

        #spinner {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            /* Màu nền */
            border-top: 4px solid var(--base-color);
            /* Màu của spinner */
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        iframe.pdf-viewer {
            width: 100%;
            height: 600px;
            /* Tăng chiều cao để dễ đọc hơn */
            border: 1px solid #ddd;
            /* Viền nhẹ để phân tách */
            border-radius: 8px;
            /* Bo góc */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            /* Hiệu ứng bóng */
            background: #f8f8f8;
            /* Màu nền nhẹ */
        }

        /* Responsive trên màn hình nhỏ */
        @media (max-width: 768px) {
            iframe.pdf-viewer {
                height: 400px;
                /* Giảm chiều cao trên mobile */
            }
        }


        /* Hiệu ứng quay */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .pdf-container {
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
            text-align: center;
            /* Căn giữa nội dung */
        }

        .pdf-container canvas {
            display: block;
            margin: 0 auto;
            /* Căn giữa canvas */
        }
    </style>
@endsection

@section('content')

    <div class="container">
        <div class="flex">
            <div class="left-section">

                <div class="box-image">

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
                        <div class="tag-group no-wrap">
                            @foreach ($tagNames as $tag)
                                <span>{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="read-time rating-container" item_code="book" data-post-id="{{ $book->id }}">
                        <span id="vote-count-{{ $book->id }}">{{ $book->vote_count }}</span> đánh giá
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="star {{ isset($book->user_vote) && $i <= $book->user_vote ? 'selected' : ($i <= $book->vote_average ? 'selected' : '') }}"
                                data-value="{{ $i }}" data-book-id="{{ $book->id }}">★</i>
                        @endfor
                        <span class="ms-2">({{ number_format($book->vote_average, 1) }} điểm)</span>
                    </div>

                    <div class="button-group">
                        <a href="#resource-section" class="btn-primary">Đọc Sách</a>
                        <a id="bookmark-btn"
                            class="{{ \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($book->id, 'book') ? 'btn-primary' : 'btn-secondary' }}"
                            data-id="{{ $book->id }}" data-code="book">
                            Bookmark
                        </a>

                        <a href="#discussion-section" class="btn-secondary">Thảo Luận</a>
                        <a href="{{ route('front.tblogs.create') }}" class="btn btn-secondary" id="share-btn"
                            data-book-url="{{ url()->current() }} ">
                            Chia sẻ
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <div class="content-section box-content">
            <h3>Tóm tắt</h3>
            <p>{!! nl2br(e($book->summary)) !!}</p>
        </div>

        <div class="content-section box-content">
            <h3>Nội dung</h3>
            <p>{!! nl2br(e($book->content)) !!}</p>
        </div>

        <div id="resource-section" class="content-section box">
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
                        <div id="pdf-container-{{ $loop->index }}" class="pdf-container my-2"></div>
                        <p><a href="{{ asset($resource->url) }}" target="_blank" class="text-blue-500 underline">Tải xuống
                                PDF</a></p>

                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                var url = "{{ asset($resource->url) }}";
                                var container = document.getElementById("pdf-container-{{ $loop->index }}");

                                pdfjsLib.getDocument(url).promise.then(function(pdf) {
                                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                                        pdf.getPage(pageNum).then(function(page) {
                                            var canvas = document.createElement("canvas");
                                            container.appendChild(canvas);
                                            var ctx = canvas.getContext('2d');

                                            var viewport = page.getViewport({
                                                scale: 1.5
                                            });
                                            canvas.width = viewport.width;
                                            canvas.height = viewport.height;

                                            var renderContext = {
                                                canvasContext: ctx,
                                                viewport: viewport
                                            };
                                            page.render(renderContext);
                                        });
                                    }
                                });
                            });
                        </script>
                    @else
                        <p>Không hỗ trợ định dạng tệp này.</p>
                    @endif
                @endforeach
            @else
                <p>Không có tài nguyên nào.</p>
            @endif
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>



        <div class="content-section box-content" id="discussion-section">
            <div class="book-comments">
                <div id="comment-section">
                    {!! $comments !!}
                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var bookmarkBtn = document.getElementById("bookmark-btn");

            bookmarkBtn.addEventListener("click", function(event) {
                event.preventDefault();

                let itemId = this.getAttribute("data-id");
                let itemCode = this.getAttribute("data-code");

                // Đổi màu trước khi gửi request để UI phản hồi nhanh
                bookmarkBtn.classList.toggle("btn-primary");
                bookmarkBtn.classList.toggle("btn-secondary");

                fetch("{{ route('front.book.bookmark') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            item_id: itemId,
                            item_code: itemCode
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.isBookmarked) {
                            // Nếu backend trả về chưa bookmark thì đổi lại UI
                            bookmarkBtn.classList.remove("btn-primary");
                            bookmarkBtn.classList.add("btn-secondary");
                        } else {
                            bookmarkBtn.classList.remove("btn-secondary");
                            bookmarkBtn.classList.add("btn-primary");
                        }
                    })
                    .catch(error => {
                        console.error("Lỗi:", error);
                        // Nếu có lỗi, revert lại class ban đầu
                        bookmarkBtn.classList.toggle("btn-primary");
                        bookmarkBtn.classList.toggle("btn-secondary");
                    });
            });
        });

        document.getElementById('share-btn').addEventListener('click', function(event) {
            let bookUrl = this.getAttribute('data-book-url'); // Lấy URL sách
            localStorage.setItem('sharedBookUrl', bookUrl); // Lưu vào localStorage
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratings = document.querySelectorAll('.rating-container');

            ratings.forEach(rating => {
                const stars = rating.querySelectorAll('.star');
                const itemId = rating.getAttribute('data-post-id');
                const itemCode = rating.getAttribute('item_code');
                
                // Kiểm tra đánh giá trong localStorage
                const savedVote = localStorage.getItem(`user-vote-${itemCode}-${itemId}`);
                
                if (savedVote) {
                    // Hiển thị đánh giá từ localStorage nếu có
                    stars.forEach((s, i) => {
                        if (i < parseInt(savedVote)) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                }

                stars.forEach((star, index) => {
                    // Xử lý hover vào sao
                    star.addEventListener('mouseover', function() {
                        // Xóa class hover cho tất cả sao
                        stars.forEach(s => s.classList.remove('hover'));
                        
                        // Thêm class hover cho sao hiện tại và các sao trước nó
                        for (let i = 0; i <= index; i++) {
                            stars[i].classList.add('hover');
                        }
                    });

                    // Xử lý khi di chuột ra khỏi sao
                    star.addEventListener('mouseout', function() {
                        stars.forEach(s => s.classList.remove('hover'));
                    });

                    // Xử lý khi click vào sao
                    star.addEventListener('click', function() {
                        const value = this.getAttribute('data-value');
                        const postId = rating.getAttribute('data-post-id');
                        const itemCode = rating.getAttribute('item_code');

                        fetch("{{ route('front.votes.vote') }}", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    point: value,
                                    item_code: itemCode,
                                    item_id: postId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Cập nhật số lượt đánh giá
                                    document.getElementById('vote-count-' + postId).innerText = data.count;
                                    
                                    // Cập nhật điểm trung bình
                                    const avgPointSpan = rating.querySelector('.ms-2');
                                    if (avgPointSpan) {
                                        avgPointSpan.textContent = `(${parseFloat(data.averagePoint).toFixed(1)} điểm)`;
                                    }

                                    // Lưu đánh giá người dùng vào localStorage
                                    localStorage.setItem(`user-vote-${itemCode}-${postId}`, value);

                                    // Cập nhật hiển thị sao theo đánh giá của người dùng
                                    stars.forEach((s, i) => {
                                        if (i < value) {
                                            s.classList.add('selected');
                                        } else {
                                            s.classList.remove('selected');
                                        }
                                    });
                                } else {
                                    alert("Bạn cần đăng nhập để đánh giá!");
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    });
                });
            });
        });
    </script>
@endsection
