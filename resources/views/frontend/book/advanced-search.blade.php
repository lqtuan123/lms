@extends('frontend.layouts.master')
@section('css')
    <style>
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px;
        }

        .page-title {
            text-align: center;
            font-weight: bold;
            color: #343a40;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-secondary {
            padding: 10px 20px;
            border-radius: 8px;
        }

        .book-item {
            text-align: center;
            transition: transform 0.3s ease;
        }

        .book-item img {
            border-radius: 8px;
            transition: 0.3s;
            width: 100px;
            height: 140px;
            object-fit: cover;
        }

        .book-item:hover {
            transform: translateY(-5px);
        }

        .book-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;

            transition: color 0.3s;
        }

        .book-title:hover {
            color: #007bff;
        }

        .book-meta {
            font-size: 12px;
            color: #6c757d;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 20px;
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
        <div class="row">
            <div class="col-md-12">
                <button onclick="history.back()" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </button>
                <h2 class="page-title mb-4">Tìm kiếm nâng cao</h2>

                <form action="{{ route('frontend.book.search') }}" method="GET">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="book_title" class="form-label">Tiêu đề sách</label>
                                    <input type="text" class="form-control" id="book_title" name="book_title"
                                        value="{{ request('book_title') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="book_type_id" class="form-label">Danh mục sách</label>
                                    <select class="form-control" id="book_type_id" name="book_type_id">
                                        <option value="">Chọn danh mục</option>
                                        @foreach ($booktypes as $booktype)
                                            <option value="{{ $booktype->id }}"
                                                {{ request('book_type_id') == $booktype->id ? 'selected' : '' }}>
                                                {{ $booktype->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="summary" class="form-label">Mô tả</label>
                                    <input type="text" class="form-control" id="summary" name="summary"
                                        value="{{ request('summary') }}" placeholder="Tìm kiếm trong phần mô tả sách...">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="tags" class="form-label">Thẻ</label>
                                    <select id="select-junk" name="tags[]" multiple placeholder=" ..." autocomplete="off">
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag->id }}"
                                                {{ in_array($tag->id, (array) request('tags', [])) ? 'selected' : '' }}>
                                                {{ $tag->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Có thể chọn nhiều thẻ</small>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                <a href="{{ route('frontend.book.advanced-search') }}" class="btn btn-secondary">Đặt lại</a>
                            </div>
                        </div>
                    </div>
                </form>

                @if (isset($books))
                    <div class="search-results mt-4">
                        <h3>Kết quả tìm kiếm ({{ $books->total() }} kết quả)</h3>

                        @if ($books->count() > 0)
                            <div class="row row-cols-2 row-cols-sm-4 row-cols-md-6 row-cols-lg-8 g-3">
                                @foreach ($books as $book)
                                    <div class=" mb-4">
                                        <div class="book-item">
                                            <a href="{{ route('front.book.show', $book->slug) }}"
                                                class="text-dark text-decoration-none book-link"
                                                data-book-id="{{ $book->id }}">
                                                <img src="{{ $book->photo }}" alt="{{ $book->title }}"
                                                    class="img-fluid">
                                                <h5 class="book-title mt-2">{{ Str::limit($book->title, 15) }}</h5>
                                            </a>
                                            <div class="book-meta">
                                                <span class="author">{{ $book->author }}</span>
                                                <span class="date">{{ $book->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pagination-wrapper">
                                {{ $books->appends(request()->all())->links() }}
                            </div>
                        @else
                            <p>Không tìm thấy kết quả nào.</p>
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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

            // Xóa lựa chọn ban đầu nếu cần
            setTimeout(() => {
                select.clear();
            }, 100);
        });
    </script>
@endsection
