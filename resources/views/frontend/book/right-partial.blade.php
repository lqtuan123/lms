<div class="right-partial" style="max-width: 300px;">
    <!-- Danh mục sách -->
    <div class="book-category-list mb-4">
        <h5 class="text-center">Danh mục sách</h5>
        <ul class="list-group">
            @forelse ($booktypes as $booktype)
                <li class="list-group-item">
                    <a href="{{ route('front.book.byType', $booktype->slug) }}" class="text-dark">
                        {{ $booktype->title }} ({{ $booktype->books_count }})
                    </a>
                </li>
            @empty
                <li class="list-group-item text-muted">Không có danh mục nào</li>
            @endforelse
        </ul>
    </div>
    
    <!-- Sách nổi bật -->
    <div class="featured-books mb-4">
        <h5 class="text-center">Sách nổi bật</h5>
        @forelse ($featuredBooks as $book)
            <div class="d-flex mb-3">
                <img src="{{ $book->photo }}" alt="{{ $book->title }}" style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px;">
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
    
    <!-- Quảng cáo hoặc banner -->
    <div class="advertisement text-center">
        <h6 class="mb-3">Quảng cáo</h6>
        <img src="{{ asset('images/ad-banner.jpg') }}" alt="Quảng cáo" class="img-fluid rounded">
    </div>
</div>
