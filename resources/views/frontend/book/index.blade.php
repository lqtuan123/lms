@extends('frontend.layouts.master')
@section('content')
<div class="container py-5">
    <h2 class="h4 font-weight-bold mb-4">Tất cả Sách</h2>
    <style>
        .custom-badge {
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 75%;
            font-weight: bold;
            color: #fff;
            background-color: #007bff; /* Màu xanh như badge-primary */
            border-radius: 0.25rem;
        }
    </style>
    <!-- Danh sách sách -->
    <div class="row">
        @forelse ($books as $book)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <!-- Ảnh sách -->
                    <div class="book-image text-center">
                        <a href="{{ route('front.book.show', $book->slug) }}" title="{{ $book->title }}">
                            <img src="{{ $book->photo }}" alt="{{ $book->title }}" class="card-img-top img-fluid" style="height: 200px; object-fit: cover;">
                        </a>
                    </div>

                    <!-- Thông tin sách -->
                    <div class="card-body d-flex flex-column">
                        <!-- Tiêu đề -->
                        <h5 class="card-title text-truncate">
                            <a href="{{ route('front.book.show', $book->slug) }}" class="text-dark text-decoration-none">
                                {{ $book->title }}
                            </a>
                        </h5>

                        <!-- Tóm tắt -->
                        <p class="card-text text-muted small mb-2">
                            {{ \Illuminate\Support\Str::limit($book->summary, 100, '...') }}
                        </p>

                        <!-- Tác giả và Loại sách -->
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <!-- Tác giả -->
                            <div class="d-flex align-items-center text-muted small">
                                <i class="fa fa-user mr-1"></i>
                                <a href="#" class="text-muted text-decoration-none">
                                    {{ $book->user ? $book->user->full_name : 'N/A' }}
                                </a>
                            </div>

                            <!-- Loại sách -->
                            <span class="custom-badge">
                                {{ $book->bookType ? $book->bookType->title : 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="col-12 text-center text-muted">Không có sách nào được tìm thấy.</p>
        @endforelse
    </div>
</div>

@endsection
@section('scripts')
<script src="{{asset('frontend/assets/js/timer.js')}}"></script>
@endsection