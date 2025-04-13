<aside class="col-md-3">
    <div class="sidebar ps-lg-5">

        {{-- Tìm kiếm --}}
        <div class="widget-menu">
            <div class="widget-search-bar">
                <form class="d-flex border rounded-3 p-2" role="search" action="{{ route('front.book.search') }}"
                    method="GET">
                    <input class="form-control border-0 me-2 py-2" type="search" name="book_title" placeholder="Search"
                        aria-label="Search" {{ request('book_title') }}>
                    <button class="btn rounded-3 p-3 d-flex align-items-center" type="submit">
                        <svg class="search text-light" width="18" height="18">
                            <use xlink:href="#search"></use>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Danh mục sách --}}
        <div class="widget-product-categories pt-5">
            <div class="section-title overflow-hidden mb-2">
                <h3 class="d-flex flex-column mb-0">Categories</h3>
            </div>
            <ul class="product-categories mb-0 sidebar-list list-unstyled">
                <li class="cat-item">
                    <a href="{{ route('front.book.index') }}">All</a>
                </li>
                @foreach ($booktypes->take(10) as $booktype)
                    <li class="cat-item">
                        <a href="{{ route('front.book.byType', $booktype->slug) }}">
                            {{ $booktype->title }} ({{ $booktype->active_books_count }})
                        </a>
                    </li>
                @endforeach
                <li class="cat-item">
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#bookTypeModal">Xem thêm ...</a>
                </li>
            </ul>
        </div>

        {{-- Modal chọn loại sách --}}
        @include('frontend.book.typebook')

        {{-- Sách vừa đọc --}}
        <div class="widget-product-categories pt-5">
            <div class="section-title overflow-hidden mb-2">
                <a href="{{ route('front.book.recentBook') }}">
                    <h3 class="d-flex flex-column mb-0">Sách vừa đọc</h3>
                </a>
            </div>
            <div class="featured-books">
                @forelse ($recentBooks as $book)
                    <div class="d-flex mb-3">
                        <a href="{{ route('front.book.show', $book->slug) }}" class="me-2">
                            <img src="{{ $book->photo }}" alt="{{ $book->title }}"
                                style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px;">
                        </a>
                        <div class="ms-2">
                            <a href="{{ route('front.book.show', $book->slug) }}" class="text-dark d-block">
                                {{ Str::limit($book->title, 50) }}
                            </a>
                            <small class="text-muted">{{ $book->user->full_name ?? 'N/A' }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">Không có sách vừa đọc</p>
                @endforelse
            </div>
        </div>


        {{-- Sách nổi bật --}}
        <div class="widget-product-categories pt-5">
            <div class="section-title overflow-hidden mb-2">
                <a href="{{ route('front.book.index', ['sort' => 'rating_desc']) }}">
                    <h3 class="d-flex flex-column mb-0">Sách nổi bật</h3>
                </a>
            </div>
            <div class="featured-books">
                @forelse ($recommendedBooks as $book)
                    <div class="d-flex mb-3">
                        <img src="{{ $book->photo }}" alt="{{ $book->title }}"
                            style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px;">
                        <div class="ms-2">
                            <a href="{{ route('front.book.show', $book->slug) }}" class="text-dark d-block">
                                {{ Str::limit($book->title, 50) }}
                            </a>
                            <small class="text-muted">{{ $book->user->full_name ?? 'N/A' }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">Không có sách nổi bật</p>
                @endforelse
            </div>
        </div>

        {{-- Quảng cáo --}}
        <div class="widget-product-categories pt-5 text-center">
            <h6 class="mb-3">Quảng cáo</h6>
            <img src="{{ asset('images/ad-banner.jpg') }}" alt="Quảng cáo" class="img-fluid rounded">
        </div>

    </div>
</aside>
