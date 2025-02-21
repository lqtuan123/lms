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

        .book-category {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
        }

        .book-card {
            background: #fff;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
        }

        .book-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }

        .book-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }

        .book-summary {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-top: 8px;
        }

        .custom-badge {
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border-radius: 5px;
        }

        .main-content {
            display: flex;
            flex-wrap: wrap;
        }

        .left-content {
            flex: 3;
            padding-right: 20px;
        }

        .right-partial {
            flex: 1;
            max-width: 300px;
            padding: 15px;
            background: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            height: fit-content;
        }

        .book-item img {
            border-radius: 8px;
            transition: 0.3s;
            width: 100px;
            height: 140px;
            object-fit: cover;
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
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('front.book.search') }}" method="GET">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="title" class="form-control"
                                        placeholder="Nhập tên sách cần tìm..." value="{{ request('title') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                    <a href="{{ route('frontend.book.advanced-search') }}" class="btn btn-secondary">Tìm
                                        kiếm nâng cao</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-content">
            <div class="left-content">

                <!-- Tất cả sách -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <button onclick="history.back()" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </button>
                            <div class="card-header">
                                <h4 class="mb-0">Sách {{ $bookType->title }}</h4>
                            </div>
                            <div class="card-body">
                                @if ($books->count() > 0)
                                    <div class="row">
                                        @foreach ($books as $book)
                                            <div class="col-md-2">
                                                <div class="book-item">
                                                    <a href="{{ route('front.book.show', $book->slug) }}"
                                                        class="text-dark text-decoration-none book-link"
                                                        data-book-id="{{ $book->id }}">
                                                        <img src="{{ $book->photo }}" alt="{{ $book->title }}"
                                                            class="img-fluid">
                                                        <h6 class="book-title mt-2 " alt="{{ $book->title }}">
                                                            {{ Str::limit($book->title, 15) }} </h6>
                                                    </a>
                                                    <div class="book-meta">
                                                        <span class="author">{{ $book->author }}</span>
                                                        <span
                                                            class="date">{{ $book->created_at->format('d/m/Y') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="pagination-wrapper">
                                        {{ $books->links() }}
                                    </div>
                                @else
                                    <p>Chưa có sách nào.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="right-partial">
                @include('frontend.book.right-partial')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".book-link").forEach(item => {
                item.addEventListener("click", function(event) {
                    event.preventDefault();
                    let bookId = this.getAttribute("data-book-id");
                    fetch("{{ route('front.book.markAsRead') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            book_id: bookId
                        })
                    }).then(() => {
                        window.location.href = this.href;
                    });
                });
            });
        });
    </script>
@endsection
