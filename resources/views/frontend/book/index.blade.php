@extends('frontend.layouts.master')

@section('content')
    <section class="hero-section position-relative padding-large"
        style="background-image: url('{{ asset('frontend/assets_f/images/banner-image-bg-1.jpg') }}');
    background-size: cover; background-repeat: no-repeat; background-position: center; height: 400px;">
        <div class="hero-content">
            <div class="container">
                <div class="row">
                    <div class="text-center">
                        <h1>Book</h1>
                        <div class="breadcrumbs">
                            <span class="item">
                                <a href="{{ route('home') }}">Home > </a>
                            </span>
                            <span class="item text-decoration-underline">Book</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="shopify-grid padding-large">
        <div class="container">
            <div class="row flex-row-reverse g-md-5">
                <main class="col-md-9">
                    <div class="filter-shop d-flex flex-wrap justify-content-between mb-5">
                        <div class="showing-product">
                            @if ($books->count() > 0)
                                <p>Hiển thị {{ $books->firstItem() }}–{{ $books->lastItem() }} trong {{ $books->total() }}
                                    sách</p>
                            @else
                                <p>Không có sách nào được tìm thấy.</p>
                            @endif
                        </div>
                        <div class="sort-by">
                            <form id="sortForm" method="GET">
                                <select id="sorting" name="sort" class="form-select">
                                    <option value="">Default sorting</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A -
                                        Z)</option>
                                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z
                                        - A)</option>
                                    <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>
                                        Rating (Highest)</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="row product-content product-store">
                        @forelse ($books as $book)
                            <div class="col-lg-3 col-md-4 mb-4">
                                <div class="card position-relative p-4 border rounded-3">
                                    <a href="{{ route('front.book.show', $book->slug) }}" class="text-decoration-none">
                                        <img src="{{ $book->photo }}" class="img-fluid shadow-sm" alt="product item">
                                        <h6 class="mt-4 mb-0 fw-bold">
                                            {{ Str::limit($book->title, 30) }}
                                        </h6>
                                    </a>

                                    <div class="review-content d-flex mt-2">
                                        <p class="me-2 fs-6 text-black-50">
                                            {{ Str::limit($book->user ? $book->user->full_name : 'Tác giả ẩn danh', 7) }}
                                        </p>
                                        <div class="rating text-warning d-flex align-items-center ms-auto">
                                            @php
                                                $avgRating = $book->vote_average ?? 0;
                                                $fullStars = floor($avgRating);
                                                $halfStar = $avgRating - $fullStars >= 0.5;
                                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                            @endphp
                                            
                                            @for ($i = 0; $i < $fullStars; $i++)
                                                <svg class="star star-fill text-warning">
                                                    <use xlink:href="#star-fill"></use>
                                                </svg>
                                            @endfor
                                            
                                            @if ($halfStar)
                                                <svg class="star star-half text-warning">
                                                    <use xlink:href="#star-half"></use>
                                                </svg>
                                            @endif
                                            
                                            @for ($i = 0; $i < $emptyStars; $i++)
                                                <svg class="star star-empty text-secondary">
                                                    <use xlink:href="#star-fill"></use>
                                                </svg>
                                            @endfor
                                            
                                            <span class="ms-1">({{ $book->vote_count ?? 0 }})</span>
                                        </div>
                                    </div>

                                    <span><i class="fa fa-eye"></i> {{ $book->views }} lượt xem</span>

                                    <div class="card-concern position-absolute start-0 end-0 d-flex gap-2">
                                        <a href="{{ route('front.book.show', $book->slug) }}" class="btn btn-dark"
                                            title="Xem chi tiết">
                                            <svg class="book-open">
                                                <use xlink:href="#book-open"></use>
                                            </svg>
                                        </a>
                                        @php
                                            $isBookmarked = in_array($book->id, $bookmarkedIds);
                                        @endphp

                                        <a href="javascript:void(0)" class="btn btn-dark bookmark-btn"
                                            data-id="{{ $book->id }}" data-code="book" title="Đánh dấu yêu thích">
                                            <svg class="wishlist {{ $isBookmarked ? 'text-danger' : '' }}">
                                                <use xlink:href="#heart"></use>
                                            </svg>
                                        </a>


                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">Không tìm thấy sách nào.</p>
                        @endforelse
                    </div>

                    @if ($books->hasPages())
                        <nav class="py-5" aria-label="Page navigation">
                            <ul class="pagination justify-content-center gap-4">
                                {{-- Previous Page Link --}}
                                @if ($books->onFirstPage())
                                    <li class="page-item disabled">
                                        <a class="page-link">Prev</a>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $books->previousPageUrl() }}">Prev</a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($books->getUrlRange(1, $books->lastPage()) as $page => $url)
                                    @if ($page == $books->currentPage())
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($books->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $books->nextPageUrl() }}">Next</a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <a class="page-link">Next</a>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </main>

                @include('frontend.book.right-partial')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            fetchBooks(url);
        });

        function fetchBooks(url) {
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function() {
                    $('.book-loader').show();
                },
                success: function(data) {
                    let content = $(data).find('.product-content').html();
                    let pagination = $(data).find('.pagination').html();

                    if (content) {
                        $('.product-content').html(content);
                    }
                    if (pagination) {
                        $('.pagination').html(pagination);
                    }
                },
                complete: function() {
                    $('.book-loader').hide();
                }
            });
        }
        $('#sorting').on('change', function() {
            const sort = $(this).val();
            const url = `{{ route('front.book.index') }}?sort=${sort}`;
            fetchBooks(url);
        });


        document.addEventListener("DOMContentLoaded", function() {
            const bookmarkButtons = document.querySelectorAll(".bookmark-btn");

            bookmarkButtons.forEach(function(btn) {
                btn.addEventListener("click", function(event) {
                    event.preventDefault();

                    const itemId = this.getAttribute("data-id");
                    const itemCode = this.getAttribute("data-code");
                    const svg = this.querySelector("svg");

                    // Hiệu ứng ngay lập tức cho cảm giác phản hồi nhanh
                    svg.classList.toggle("text-danger");

                    fetch("{{ route('front.book.bookmark') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                item_id: itemId,
                                item_code: itemCode,
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (!data.isBookmarked) {
                                svg.classList.remove("text-danger");
                            } else {
                                svg.classList.add("text-danger");
                            }
                        })
                        .catch((error) => {
                            console.error("Lỗi:", error);
                            // Revert lại nếu lỗi
                            svg.classList.toggle("text-danger");
                        });
                });
            });
        });
    </script>
@endsection
