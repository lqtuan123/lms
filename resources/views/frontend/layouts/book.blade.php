<?php
// Get 5 random books
$featuredBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->inRandomOrder()
    ->limit(12)
    ->get();

// Get 5 newest books
$latestBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->orderBy('created_at', 'desc')
    ->limit(12)
    ->get();

// Get 5 most viewed books
$mostViewedBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->orderBy('views', 'desc')
    ->limit(12)
    ->get();

// Get 5 highest rated books
$highestRatedBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->get()
    ->sortByDesc(function ($book) {
        return $book->average_rating; // sử dụng accessor đã có sẵn
        return $book->rating_count; // sử dụng accessor đã có sẵn
    })
    ->take(8)
    ->values();

$books = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->orderBy('id')
    ->limit(10)
    ->get();

// Thêm đánh giá cho tất cả các danh sách sách
$latestBooks = $latestBooks->map(function ($book) {
    $voteItem = \App\Models\Rating::where('book_id', $book->id)->first();
    $book->average_rating = $voteItem?->point ?? 0;
    $book->rating_count = $voteItem?->count ?? 0;
    return $book;
});

$featuredBooks = $featuredBooks->map(function ($book) {
    $voteItem = \App\Models\Rating::where('book_id', $book->id)->first();
    $book->average_rating = $voteItem?->point ?? 0;
    $book->rating_count = $voteItem?->count ?? 0;
    return $book;
});

$mostViewedBooks = $mostViewedBooks->map(function ($book) {
    $voteItem = \App\Models\Rating::where('book_id', $book->id)->first();
    $book->average_rating = $voteItem?->point ?? 0;
    $book->rating_count = $voteItem?->count ?? 0;
    return $book;
});

// Cập nhật thêm đánh giá cho sách đã đọc gần đây nếu có
if (isset($recentBooks) && $recentBooks->count() > 0) {
    $recentBooks = $recentBooks->map(function ($book) {
        $voteItem = \App\Models\Rating::where('book_id', $book->id)->first();
        $book->average_rating = $voteItem?->point ?? 0;
        $book->rating_count = $voteItem?->count ?? 0;
        return $book;
    });
}
?>
<!-- Recommended Books Section -->
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="relative text-2xl font-bold text-gray-800 pl-4 pb-1">
            <span
                class="absolute left-0 top-1 h-full w-1 bg-gradient-to-b from-blue-600 to-indigo-600 rounded-full"></span>
            Sách được đề xuất
        </h2>
        <a href="{{ route('front.book.index') }}"
            class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-all duration-300 hover:pl-2">
            Xem thêm <i class="fas fa-chevron-right ml-1.5 text-xs"></i>
        </a>
    </div>

    <div class="featured-books-carousel relative bg-gray-50 rounded-xl p-4 shadow-sm">
        <!-- Slides Container -->
        <div class="featured-slides overflow-x-auto scroll-smooth cursor-grab book-scroll"
            style="touch-action: pan-x;">
            <!-- Slide 1 -->
            <div class="slide grid grid-cols-1 md:grid-cols-2 gap-6 min-w-full w-full flex-shrink-0 flex-grow-0">
                @foreach ($featuredBooks->take(4) as $book)
                    <div
                        class="book-card flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition border border-gray-100">
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="relative block w-[120px] h-[160px] shrink-0 overflow-hidden group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div
                                    class="text-xs text-white bg-blue-600/80 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                                    <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-col justify-between p-3 w-full overflow-hidden">
                            <div>
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600 text-lg line-clamp-2 transition-colors">{{ $book->title }}</a>
                                <p class="text-sm text-gray-500 mt-1.5">
                                    {{ $book->user ? $book->user->name : 'Unknown' }}</p>

                                @php
                                    $shortDescription =
                                        $book->summary ??
                                        'Hư giả giới thiệu văn tất: Làm Phàm: Sư huynh, sư nương gả sư phó, người thật nguyên ý thấy sư nương chịu khổ...';
                                    if (strlen($shortDescription) > 100) {
                                        $shortDescription = mb_substr($shortDescription, 0, 100) . '...';
                                    }
                                @endphp
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $shortDescription }}</p>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center">
                                    <div class="text-xs text-blue-500 font-medium">
                                        <i
                                            class="fas fa-star text-yellow-400 mr-1"></i>{{ number_format($book->average_rating, 1) }}
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 text-xs bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-medium rounded-lg border border-blue-100">
                                    @if ($book->bookType)
                                        {{ $book->bookType->name }}
                                    @else
                                        Huyền Huyễn
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Slide 2 -->
            <div class="slide grid grid-cols-1 md:grid-cols-2 gap-6 min-w-full w-full flex-shrink-0 flex-grow-0">
                @foreach ($featuredBooks->slice(4, 4) as $book)
                    <div
                        class="book-card flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition border border-gray-100">
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="relative block w-[120px] h-[160px] shrink-0 overflow-hidden group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div
                                    class="text-xs text-white bg-blue-600/80 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                                    <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-col justify-between p-3 w-full overflow-hidden">
                            <div>
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600 text-lg line-clamp-2 transition-colors">{{ $book->title }}</a>
                                <p class="text-sm text-gray-500 mt-1.5">
                                    {{ $book->user ? $book->user->name : 'Unknown' }}</p>

                                @php
                                    $shortDescription =
                                        $book->summary ??
                                        'Hư giả giới thiệu văn tất: Làm Phàm: Sư huynh, sư nương gả sư phó, người thật nguyên ý thấy sư nương chịu khổ...';
                                    if (strlen($shortDescription) > 100) {
                                        $shortDescription = mb_substr($shortDescription, 0, 100) . '...';
                                    }
                                @endphp
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $shortDescription }}</p>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center">
                                    <div class="text-xs text-blue-500 font-medium">
                                        <i
                                            class="fas fa-star text-yellow-400 mr-1"></i>{{ number_format($book->average_rating, 1) }}
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 text-xs bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-medium rounded-lg border border-blue-100">
                                    @if ($book->bookType)
                                        {{ $book->bookType->name }}
                                    @else
                                        Huyền Huyễn
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Slide 3 -->
            <div class="slide grid grid-cols-1 md:grid-cols-2 gap-6 min-w-full w-full flex-shrink-0 flex-grow-0">
                @foreach ($featuredBooks->slice(8, 4) as $book)
                    <div
                        class="book-card flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition border border-gray-100">
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="relative block w-[120px] h-[160px] shrink-0 overflow-hidden group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div
                                    class="text-xs text-white bg-blue-600/80 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                                    <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-col justify-between p-3 w-full overflow-hidden">
                            <div>
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600 text-lg line-clamp-2 transition-colors">{{ $book->title }}</a>
                                <p class="text-sm text-gray-500 mt-1.5">
                                    {{ $book->user ? $book->user->name : 'Unknown' }}</p>

                                @php
                                    $shortDescription =
                                        $book->summary ??
                                        'Hư giả giới thiệu văn tất: Làm Phàm: Sư huynh, sư nương gả sư phó, người thật nguyên ý thấy sư nương chịu khổ...';
                                    if (strlen($shortDescription) > 100) {
                                        $shortDescription = mb_substr($shortDescription, 0, 100) . '...';
                                    }
                                @endphp
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $shortDescription }}</p>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center">
                                    <div class="text-xs text-blue-500 font-medium">
                                        <i
                                            class="fas fa-star text-yellow-400 mr-1"></i>{{ number_format($book->average_rating, 1) }}
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 text-xs bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-medium rounded-lg border border-blue-100">
                                    @if ($book->bookType)
                                        {{ $book->bookType->name }}
                                    @else
                                        Huyền Huyễn
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination Dots -->
        <div class="flex justify-center mt-6 space-x-2 items-center">
            <div class="pagination-dot rounded-full bg-blue-500 active"></div>
            <div class="pagination-dot rounded-full bg-gray-300"></div>
            <div class="pagination-dot rounded-full bg-gray-300"></div>
        </div>
    </div>
</section>

<!-- Newest Books Section -->
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="relative text-2xl font-bold text-gray-800 pl-4 pb-1">
            <span
                class="absolute left-0 top-1 h-full w-1 bg-gradient-to-b from-purple-600 to-pink-600 rounded-full"></span>
            Sách mới nhất
        </h2>
        <a href="{{ route('front.book.index') }}"
            class="text-purple-600 hover:text-purple-700 font-medium flex items-center transition-all duration-300 hover:pl-2">
            Xem thêm <i class="fas fa-chevron-right ml-1.5 text-xs"></i>
        </a>
    </div>

    <div class="newest-books-carousel relative bg-gray-50 rounded-xl p-4 shadow-sm">
        <!-- Slides Container -->
        <div class="newest-slides overflow-x-auto scroll-smooth cursor-grab book-scroll"
            style="touch-action: pan-x;">
            <!-- Slide 1 -->
            <div class="slide grid grid-cols-1 md:grid-cols-2 gap-6 min-w-full w-full flex-shrink-0 flex-grow-0">
                @foreach ($latestBooks->take(4) as $book)
                    <div
                        class="book-card flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition border border-gray-100">
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="relative block w-[120px] h-[160px] shrink-0 overflow-hidden group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div
                                    class="text-xs text-white bg-blue-600/80 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                                    <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-col justify-between p-3 w-full overflow-hidden">
                            <div>
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600 text-lg line-clamp-2 transition-colors">{{ $book->title }}</a>
                                <p class="text-sm text-gray-500 mt-1.5">
                                    {{ $book->user ? $book->user->name : 'Unknown' }}</p>

                                @php
                                    $shortDescription =
                                        $book->summary ??
                                        'Hư giả giới thiệu văn tất: Làm Phàm: Sư huynh, sư nương gả sư phó, người thật nguyên ý thấy sư nương chịu khổ...';
                                    if (strlen($shortDescription) > 100) {
                                        $shortDescription = mb_substr($shortDescription, 0, 100) . '...';
                                    }
                                @endphp
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $shortDescription }}</p>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center">
                                    <div class="text-xs text-blue-500 font-medium">
                                        <i
                                            class="fas fa-star text-yellow-400 mr-1"></i>{{ number_format($book->average_rating, 1) }}
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 text-xs bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-medium rounded-lg border border-blue-100">
                                    @if ($book->bookType)
                                        {{ $book->bookType->name }}
                                    @else
                                        Huyền Huyễn
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Slide 2 -->
            <div class="slide grid grid-cols-1 md:grid-cols-2 gap-6 min-w-full w-full flex-shrink-0 flex-grow-0">
                @foreach ($latestBooks->slice(4, 4) as $book)
                    <div
                        class="book-card flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition border border-gray-100">
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="relative block w-[120px] h-[160px] shrink-0 overflow-hidden group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div
                                    class="text-xs text-white bg-blue-600/80 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                                    <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-col justify-between p-3 w-full overflow-hidden">
                            <div>
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600 text-lg line-clamp-2 transition-colors">{{ $book->title }}</a>
                                <p class="text-sm text-gray-500 mt-1.5">
                                    {{ $book->user ? $book->user->name : 'Unknown' }}</p>

                                @php
                                    $shortDescription =
                                        $book->summary ??
                                        'Hư giả giới thiệu văn tất: Làm Phàm: Sư huynh, sư nương gả sư phó, người thật nguyên ý thấy sư nương chịu khổ...';
                                    if (strlen($shortDescription) > 100) {
                                        $shortDescription = mb_substr($shortDescription, 0, 100) . '...';
                                    }
                                @endphp
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $shortDescription }}</p>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center">
                                    <div class="text-xs text-blue-500 font-medium">
                                        <i
                                            class="fas fa-star text-yellow-400 mr-1"></i>{{ number_format($book->average_rating, 1) }}
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 text-xs bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-medium rounded-lg border border-blue-100">
                                    @if ($book->bookType)
                                        {{ $book->bookType->name }}
                                    @else
                                        Huyền Huyễn
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination Dots -->
        <div class="flex justify-center mt-6 space-x-2 items-center">
            <div class="pagination-dot rounded-full bg-blue-500 active"></div>
            <div class="pagination-dot rounded-full bg-gray-300"></div>
        </div>
    </div>
</section>

<!-- Most Viewed Books Section -->
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="relative text-2xl font-bold text-gray-800 pl-4 pb-1">
            <span
                class="absolute left-0 top-1 h-full w-1 bg-gradient-to-b from-indigo-600 to-blue-600 rounded-full"></span>
            Sách được xem nhiều
        </h2>
        <a href="{{ route('front.book.index') }}"
            class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center transition-all duration-300 hover:pl-2">
            Xem thêm <i class="fas fa-chevron-right ml-1.5 text-xs"></i>
        </a>
    </div>

    <div class="viewed-books-carousel relative bg-gray-50 rounded-xl p-4 shadow-sm">
        <!-- Slides Container -->
        <div class="viewed-slides overflow-x-auto scroll-smooth cursor-grab book-scroll"
            style="touch-action: pan-x;">
            <!-- Slide 1 -->
            <div class="slide grid grid-cols-1 md:grid-cols-2 gap-6 min-w-full w-full flex-shrink-0 flex-grow-0">
                @foreach ($mostViewedBooks->take(4) as $book)
                    <div
                        class="book-card flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition border border-gray-100">
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="relative block w-[120px] h-[160px] shrink-0 overflow-hidden group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div
                                    class="text-xs text-white bg-blue-600/80 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                                    <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-col justify-between p-3 w-full overflow-hidden">
                            <div>
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600 text-lg line-clamp-2 transition-colors">{{ $book->title }}</a>
                                <p class="text-sm text-gray-500 mt-1.5">
                                    {{ $book->user ? $book->user->name : 'Unknown' }}</p>

                                @php
                                    $shortDescription =
                                        $book->summary ??
                                        'Hư giả giới thiệu văn tất: Làm Phàm: Sư huynh, sư nương gả sư phó, người thật nguyên ý thấy sư nương chịu khổ...';
                                    if (strlen($shortDescription) > 100) {
                                        $shortDescription = mb_substr($shortDescription, 0, 100) . '...';
                                    }
                                @endphp
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $shortDescription }}</p>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center">
                                    <div class="text-xs text-blue-500 font-medium">
                                        <i
                                            class="fas fa-star text-yellow-400 mr-1"></i>{{ number_format($book->average_rating, 1) }}
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 text-xs bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-medium rounded-lg border border-blue-100">
                                    @if ($book->bookType)
                                        {{ $book->bookType->name }}
                                    @else
                                        Huyền Huyễn
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Slide 2 -->
            <div class="slide grid grid-cols-1 md:grid-cols-2 gap-6 min-w-full w-full flex-shrink-0 flex-grow-0">
                @foreach ($mostViewedBooks->slice(4, 4) as $book)
                    <div
                        class="book-card flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition border border-gray-100">
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="relative block w-[120px] h-[160px] shrink-0 overflow-hidden group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 p-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <div
                                    class="text-xs text-white bg-blue-600/80 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                                    <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-col justify-between p-3 w-full overflow-hidden">
                            <div>
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600 text-lg line-clamp-2 transition-colors">{{ $book->title }}</a>
                                <p class="text-sm text-gray-500 mt-1.5">
                                    {{ $book->user ? $book->user->name : 'Unknown' }}</p>

                                @php
                                    $shortDescription =
                                        $book->summary ??
                                        'Hư giả giới thiệu văn tất: Làm Phàm: Sư huynh, sư nương gả sư phó, người thật nguyên ý thấy sư nương chịu khổ...';
                                    if (strlen($shortDescription) > 100) {
                                        $shortDescription = mb_substr($shortDescription, 0, 100) . '...';
                                    }
                                @endphp
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $shortDescription }}</p>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center">
                                    <div class="text-xs text-blue-500 font-medium">
                                        <i
                                            class="fas fa-star text-yellow-400 mr-1"></i>{{ number_format($book->average_rating, 1) }}
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 text-xs bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-medium rounded-lg border border-blue-100">
                                    @if ($book->bookType)
                                        {{ $book->bookType->name }}
                                    @else
                                        Huyền Huyễn
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination Dots -->
        <div class="flex justify-center mt-6 space-x-2 items-center">
            <div class="pagination-dot rounded-full bg-blue-500 active"></div>
            <div class="pagination-dot rounded-full bg-gray-300"></div>
        </div>
    </div>
</section>

<!-- Recently Read Section (Conditional) -->
@if (isset($recentBooks) && $recentBooks->count() > 0)
    <section class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="relative text-2xl font-bold text-gray-800 pl-4 pb-1">
                <span
                    class="absolute left-0 top-1 h-full w-1 bg-gradient-to-b from-green-600 to-teal-600 rounded-full"></span>
                Sách đang đọc gần đây
            </h2>
            <a href="{{ route('front.book.recentBook') }}"
                class="text-green-600 hover:text-green-700 font-medium flex items-center transition-all duration-300 hover:pl-2">
                Xem tất cả <i class="fas fa-chevron-right ml-1.5 text-xs"></i>
            </a>
        </div>

        <div class="book-slider relative bg-gray-50 rounded-xl p-4 shadow-sm">
            <div class="flex space-x-6 overflow-x-auto scroll-smooth cursor-grab book-scroll">
                @foreach ($recentBooks as $book)
                    <div
                        class="book-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition min-w-[162px] transform hover:scale-105 duration-300 border border-gray-100">
                        <div class="relative w-[162px] h-[216px] group">
                            <img src="{{ $book->photo ? asset($book->photo) : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80' }}"
                                alt="{{ $book->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>

                            <!-- Badges với thiết kế mới -->
                            <div
                                class="absolute top-2 left-2 bg-black/60 text-white px-2 py-1 rounded-md text-xs backdrop-blur-sm">
                                <i class="fas fa-eye mr-1"></i>{{ number_format($book->views) }}
                            </div>
                            <div
                                class="absolute top-2 right-2 bg-black/60 text-white px-2 py-1 rounded-md text-xs backdrop-blur-sm">
                                <span class="text-yellow-400">{{ number_format($book->average_rating, 1) }}</span>
                                <i class="fas fa-star text-yellow-400 ml-1"></i>
                            </div>

                            <!-- Progress bar với hiệu ứng gradient -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gray-200 h-1.5">
                                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-1.5"
                                    style="width: 45%"></div>
                            </div>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('front.book.show', $book->slug) }}"
                                class="font-medium text-gray-800 hover:text-blue-600 truncate block transition-colors">{{ $book->title }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<style>
    /* Thêm một số style CSS mới cho cards */
    .book-card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        transform: translateZ(0);
        backface-visibility: hidden;
        -webkit-font-smoothing: subpixel-antialiased;
    }

    .book-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Các style hiện có */
    .book-scroll {
        -ms-overflow-style: none;
        scrollbar-width: none;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        cursor: grab;
        overflow-x: auto;
        overflow-y: hidden;
        will-change: transform, scroll-position;
        scroll-snap-type: x proximity;
        scroll-padding: 0.5rem;
        transition: all 0.1s ease;
        position: relative;
        padding: 8px 0;
    }

    .book-scroll::-webkit-scrollbar {
        display: none;
    }

    .book-scroll.grabbing {
        cursor: grabbing !important;
        scroll-behavior: auto;
    }

    /* Thêm một đường mờ ở rìa để chỉ ra còn nội dung để cuộn */
    .book-slider {
        position: relative;
        overflow: hidden;
        margin: 0 -8px;
        padding: 0 8px;
    }

    .book-slider::after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 60px;
        background: linear-gradient(to right, rgba(255, 255, 255, 0), rgba(255, 255, 255, 1));
        pointer-events: none;
        opacity: 0.8;
        z-index: 2;
    }

    /* Styles for the slideshow */
    .featured-books-carousel,
    .newest-books-carousel,
    .viewed-books-carousel {
        --scroll-progress: 0%;
        position: relative;
        overflow: hidden;
        margin: 0 -8px;
        padding: 0 8px;
        contain: layout style;
    }

    .featured-books-carousel::after,
    .newest-books-carousel::after,
    .viewed-books-carousel::after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 60px;
        background: linear-gradient(to right, rgba(255, 255, 255, 0), rgba(255, 255, 255, 1));
        pointer-events: none;
        opacity: 0.8;
        z-index: 2;
    }

    .featured-books-carousel::before,
    .newest-books-carousel::before,
    .viewed-books-carousel::before {
        content: "";
        position: absolute;
        bottom: -2px;
        left: 0;
        height: 2px;
        width: var(--scroll-progress);
        background: linear-gradient(to right, #3b82f6, #60a5fa);
        z-index: 10;
        opacity: 0.7;
        transition: width 0.2s ease-out;
        border-radius: 2px;
    }

    .featured-slides,
    .newest-slides,
    .viewed-slides {
        display: flex;
        scroll-snap-type: x mandatory;
        gap: 16px;
        -ms-overflow-style: none;
        scrollbar-width: none;
        scroll-behavior: smooth;
        overscroll-behavior-x: contain;
        padding: 5px 0;
        will-change: transform, scroll-position;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        transform: translate3d(0, 0, 0);
        transition: transform 0.1s ease;
    }

    .featured-slides.dragging,
    .newest-slides.dragging,
    .viewed-slides.dragging {
        scroll-behavior: auto !important;
        scroll-snap-type: none !important;
        cursor: grabbing !important;
    }

    .featured-slides::-webkit-scrollbar,
    .newest-slides::-webkit-scrollbar,
    .viewed-slides::-webkit-scrollbar {
        display: none;
    }

    .featured-slides .slide,
    .newest-slides .slide,
    .viewed-slides .slide {
        scroll-snap-align: start;
        flex-shrink: 0;
        flex-grow: 0;
        width: 100%;
        scroll-snap-stop: always;
        transform: translateZ(0);
        backface-visibility: hidden;
        perspective: 1000px;
        -webkit-font-smoothing: subpixel-antialiased;
        contain: content;
    }

    .featured-slides .book-card,
    .newest-slides .book-card,
    .viewed-slides .book-card {
        transform: translateZ(0);
        transition: all 0.25s ease-out;
        will-change: transform, box-shadow;
    }

    .featured-slides .book-card:hover,
    .newest-slides .book-card:hover,
    .viewed-slides .book-card:hover {
        transform: translateY(-4px) scale(1.02);
    }

    .pagination-dot {
        transition: background-color 0.3s ease;
        width: 8px;
        height: 8px;
    }

    .pagination-dot.active {
        background-color: #3b82f6;
        width: 24px;
        border-radius: 4px;
    }

    .pagination-dot:not(.active) {
        background-color: #d1d5db;
        cursor: pointer;
    }

    /* Indicator for swipe */
    .can-swipe::before {
        content: "";
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='rgba(37, 99, 235, 0.5)'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'%3E%3C/path%3E%3C/svg%3E");
        background-size: contain;
        animation: swipe-hint 1.5s ease-in-out infinite;
        z-index: 5;
        pointer-events: none;
        opacity: 0.7;
    }

    /* Ẩn indicator khi người dùng đã tương tác */
    .featured-slides.user-interacted::before,
    .newest-slides.user-interacted::before,
    .viewed-slides.user-interacted::before {
        display: none;
    }

    .featured-slides:active,
    .newest-slides:active,
    .viewed-slides:active {
        cursor: grabbing;
    }

    @keyframes swipe-hint {

        0%,
        100% {
            transform: translateY(-50%) translateX(0);
        }

        50% {
            transform: translateY(-50%) translateX(10px);
        }
    }

    /* Điều chỉnh CSS cho phù hợp với thiết kế mới */
    .featured-books-carousel,
    .newest-books-carousel,
    .viewed-books-carousel {
        background-color: #f9fafb;
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .featured-books-carousel::after,
    .newest-books-carousel::after,
    .viewed-books-carousel::after {
        border-radius: 0 0.75rem 0.75rem 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo tất cả các carousel
        initFeaturedCarousel();
        initNewestCarousel();
        initViewedCarousel();

        // Các chức năng cuộn mượt cho các phần trượt khác
        initSmoothScrolling();
    });

    function initFeaturedCarousel() {
        const carousel = document.querySelector('.featured-books-carousel');
        if (!carousel) return;

        const slidesContainer = carousel.querySelector('.featured-slides');
        const slides = carousel.querySelectorAll('.slide');
        const dots = carousel.querySelectorAll('.pagination-dot');

        if (!slidesContainer || slides.length === 0) return;

        let isScrolling;
        let isDown = false;
        let startX;
        let scrollLeft;
        let rafPending = false;
        let lastScrollPosition = 0;
        let lastTime = 0;
        let scrollVelocity = 0;
        let autoScrollInterval;
        let interacted = false;

        // Đảm bảo mỗi slide có đúng width 100%
        function updateSlideWidths() {
            const containerWidth = slidesContainer.clientWidth;
            slides.forEach(slide => {
                slide.style.width = `${containerWidth}px`;
            });
        }

        // Gọi hàm cập nhật kích thước ngay lập tức và khi cửa sổ thay đổi kích thước
        updateSlideWidths();

        // Sử dụng ResizeObserver để theo dõi thay đổi kích thước
        if (typeof ResizeObserver === 'function') {
            const resizeObserver = new ResizeObserver(debounce(() => {
                updateSlideWidths();
                requestAnimationFrame(() => {
                    snapToNearestSlide(false);
                    updateDots();
                });
            }, 200));

            resizeObserver.observe(slidesContainer);
            window.addEventListener('orientationchange', debounce(() => {
                updateSlideWidths();
                snapToNearestSlide(false);
            }, 200));
        } else {
            // Fallback cho trình duyệt không hỗ trợ ResizeObserver
            window.addEventListener('resize', debounce(() => {
                updateSlideWidths();
                snapToNearestSlide(false);
                updateDots();
            }, 200));
        }

        // Cập nhật dots dựa trên vị trí scroll
        function updateDots() {
            if (rafPending) return;

            rafPending = true;

            requestAnimationFrame(() => {
                const scrollPosition = slidesContainer.scrollLeft;
                const slideWidth = slides[0].offsetWidth;

                if (slideWidth === 0) {
                    rafPending = false;
                    return;
                }

                const currentIndex = Math.min(
                    Math.floor((scrollPosition + (slideWidth / 2)) / slideWidth),
                    slides.length - 1
                );

                // Cập nhật thanh tiến trình
                updateScrollProgressBar();

                // Chỉ thay đổi class khi cần thiết để tránh reflow/repaint
                dots.forEach((dot, index) => {
                    const shouldBeActive = index === currentIndex;
                    const isActive = dot.classList.contains('active');

                    if (shouldBeActive && !isActive) {
                        dot.classList.add('active', 'bg-blue-500');
                        dot.classList.remove('bg-gray-300');
                    } else if (!shouldBeActive && isActive) {
                        dot.classList.remove('active', 'bg-blue-500');
                        dot.classList.add('bg-gray-300');
                    }
                });

                rafPending = false;
            });
        }

        // Debounce hàm để tối ưu hiệu suất
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Throttle hàm scroll để giảm số lần gọi
        function throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        // Tính toán vận tốc cuộn
        function calculateVelocity(newPosition, newTime) {
            const deltaPosition = newPosition - lastScrollPosition;
            const deltaTime = newTime - lastTime;

            lastScrollPosition = newPosition;
            lastTime = newTime;

            // Tránh chia cho 0
            if (deltaTime === 0) return scrollVelocity;

            // Tính vận tốc (pixels/ms)
            return deltaPosition / deltaTime;
        }

        // Reset auto scroll interval
        function resetAutoScroll() {
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
            }

            if (!interacted) {
                autoScrollInterval = setInterval(() => {
                    const currentIndex = getCurrentSlideIndex();
                    const nextIndex = (currentIndex + 1) % slides.length;
                    scrollToSlide(nextIndex);
                }, 7000); // Thời gian dài hơn cho carousel thứ hai và ba
            }
        }

        // Lấy index của slide hiện tại
        function getCurrentSlideIndex() {
            const scrollPosition = slidesContainer.scrollLeft;
            const slideWidth = slides[0].offsetWidth;
            return Math.min(
                Math.floor((scrollPosition + (slideWidth / 2)) / slideWidth),
                slides.length - 1
            );
        }

        // Cuộn đến slide cụ thể
        function scrollToSlide(index, smooth = true) {
            const slideWidth = slidesContainer.clientWidth;
            const targetPosition = index * slideWidth;

            // Đảm bảo không scroll quá giới hạn
            const maxScrollLeft = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            const safePosition = Math.max(0, Math.min(targetPosition, maxScrollLeft));

            // Cài đặt behavior
            slidesContainer.style.scrollBehavior = smooth ? 'smooth' : 'auto';

            slidesContainer.scrollTo({
                left: safePosition,
                behavior: smooth ? 'smooth' : 'auto'
            });

            // Phục hồi scroll behavior
            setTimeout(() => {
                slidesContainer.style.scrollBehavior = 'smooth';
            }, 100);

            // Cập nhật dots sau khi cuộn
            setTimeout(updateDots, 300);
        }

        // Xử lý sự kiện pointerdown
        slidesContainer.addEventListener('pointerdown', (e) => {
            isDown = true;
            interacted = true; // Đánh dấu là đã tương tác
            slidesContainer.classList.add('dragging');
            startX = e.clientX;
            scrollLeft = slidesContainer.scrollLeft;
            lastTime = Date.now();
            lastScrollPosition = scrollLeft;

            // Dừng auto scroll
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }

            // Dừng animation đang diễn ra
            cancelAnimationFrame(isScrolling);

            // Ngăn chặn text selection khi kéo
            e.preventDefault();
        }, {
            passive: false
        });

        // Xử lý sự kiện pointermove hiệu quả hơn
        slidesContainer.addEventListener('pointermove', (e) => {
            if (!isDown) return;
            e.preventDefault();

            const x = e.clientX;
            const walk = (x - startX) * 1.5; // Hệ số kéo
            const newPosition = scrollLeft - walk;
            const newTime = Date.now();

            // Kiểm tra giới hạn
            const maxScroll = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            const boundedPosition = Math.max(0, Math.min(newPosition, maxScroll));

            // Cập nhật vị trí scroll với RAF để mượt hơn
            if (!rafPending) {
                rafPending = true;
                requestAnimationFrame(() => {
                    slidesContainer.scrollLeft = boundedPosition;
                    rafPending = false;
                });
            }

            // Tính toán vận tốc mới
            scrollVelocity = calculateVelocity(boundedPosition, newTime);
        }, {
            passive: false
        });

        // Momentum scrolling
        function momentumScroll() {
            // Giảm dần vận tốc theo đường cong mượt mà
            scrollVelocity *= 0.95;

            // Nếu vận tốc quá nhỏ, dừng lại và snap
            if (Math.abs(scrollVelocity) < 0.5) {
                snapToNearestSlide();
                return;
            }

            // Tính toán vị trí mới dựa trên vận tốc
            const newPosition = slidesContainer.scrollLeft + scrollVelocity * 10;

            // Kiểm tra giới hạn
            const maxScroll = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            if (newPosition <= 0 || newPosition >= maxScroll) {
                snapToNearestSlide();
                return;
            }

            // Cập nhật vị trí scroll với RAF để mượt hơn
            requestAnimationFrame(() => {
                slidesContainer.scrollLeft = newPosition;
                updateDots();
            });

            // Tiếp tục animation
            isScrolling = requestAnimationFrame(momentumScroll);
        }

        // Snap đến slide gần nhất
        function snapToNearestSlide(animate = true) {
            if (rafPending) return;

            const slideWidth = slidesContainer.clientWidth;
            const currentPosition = slidesContainer.scrollLeft;
            const targetIndex = Math.round(currentPosition / slideWidth);

            // Scroll đến slide gần nhất
            scrollToSlide(targetIndex, animate);

            // Reset auto scroll
            resetAutoScroll();
        }

        // Xử lý sự kiện khi kết thúc kéo
        function endDrag() {
            if (!isDown) return;

            isDown = false;
            slidesContainer.classList.remove('dragging');

            // Nếu vận tốc đủ lớn, thực hiện momentum scroll
            if (Math.abs(scrollVelocity) > 0.8) {
                isScrolling = requestAnimationFrame(momentumScroll);
            } else {
                // Nếu không, chỉ snap đến slide gần nhất
                snapToNearestSlide();
            }
        }

        slidesContainer.addEventListener('pointerup', endDrag);
        slidesContainer.addEventListener('pointerleave', endDrag);
        slidesContainer.addEventListener('pointercancel', endDrag);

        // Xử lý sự kiện cuộn thông thường
        slidesContainer.addEventListener('scroll', throttle(() => {
            if (isDown) return; // Không xử lý khi đang kéo

            // Đặt một timer để phát hiện khi nào việc cuộn kết thúc
            clearTimeout(isScrolling);
            isScrolling = setTimeout(() => {
                // Nếu đang không kéo và cuộn kết thúc, snap đến slide gần nhất
                if (!isDown) {
                    snapToNearestSlide();
                }
            }, 150);

            // Cập nhật dots trong khi cuộn
            if (!rafPending) {
                updateDots();
            }
        }, 100));

        // Cập nhật thanh tiến trình cuộn
        function updateScrollProgressBar() {
            const maxScroll = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            if (maxScroll <= 0) return;

            const currentScroll = slidesContainer.scrollLeft;
            const progressPercent = (currentScroll / maxScroll) * 100;

            // Cập nhật width của thanh tiến trình
            carousel.style.setProperty('--scroll-progress', `${progressPercent}%`);

            // Đánh dấu rằng người dùng đã tương tác
            if (currentScroll > 0 && !slidesContainer.classList.contains('user-interacted')) {
                slidesContainer.classList.add('user-interacted');
                interacted = true;
            }
        }

        // Thêm sự kiện để ẩn gợi ý kéo lướt sau lần tương tác đầu tiên
        slidesContainer.addEventListener('pointerdown', () => {
            slidesContainer.classList.add('user-interacted');
            interacted = true;
        }, {
            once: true
        });

        // Xử lý sự kiện touch cho thiết bị di động tối ưu
        slidesContainer.addEventListener('touchstart', (e) => {
            isDown = true;
            interacted = true;
            slidesContainer.classList.add('dragging');
            startX = e.touches[0].clientX;
            scrollLeft = slidesContainer.scrollLeft;
            lastTime = Date.now();
            lastScrollPosition = scrollLeft;

            // Dừng auto scroll
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }

            // Dừng animation đang diễn ra
            cancelAnimationFrame(isScrolling);
        }, {
            passive: true
        });

        slidesContainer.addEventListener('touchmove', (e) => {
            if (!isDown) return;

            const x = e.touches[0].clientX;
            const walk = (x - startX) * 1.2;
            const newPosition = scrollLeft - walk;
            const newTime = Date.now();

            // Cập nhật vị trí scroll
            slidesContainer.scrollLeft = newPosition;

            // Tính toán vận tốc mới
            scrollVelocity = calculateVelocity(newPosition, newTime);
        }, {
            passive: true
        });

        slidesContainer.addEventListener('touchend', endDrag, {
            passive: true
        });

        // Cho phép nhấp vào dots để cuộn đến slide tương ứng
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                interacted = true;
                // Dừng bất kỳ momentum scrolling nào đang diễn ra
                cancelAnimationFrame(isScrolling);

                // Dừng auto scroll
                if (autoScrollInterval) {
                    clearInterval(autoScrollInterval);
                    autoScrollInterval = null;
                }

                // Scroll đến slide tương ứng
                scrollToSlide(index);
            });
        });

        // Hỗ trợ phím mũi tên
        document.addEventListener('keydown', (e) => {
            if (!carousel.matches(':hover') && !carousel.contains(document.activeElement)) {
                return;
            }

            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const currentIndex = getCurrentSlideIndex();
                const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
                scrollToSlide(prevIndex);
                interacted = true;
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                const currentIndex = getCurrentSlideIndex();
                const nextIndex = (currentIndex + 1) % slides.length;
                scrollToSlide(nextIndex);
                interacted = true;
            }
        });

        // Thêm class để chỉ ra rằng có thể kéo được
        slidesContainer.classList.add('can-swipe');

        // Khởi tạo auto scroll
        resetAutoScroll();

        // Gọi updateDots lần đầu khi trang tải
        updateDots();

        // Tối ưu hóa khi tab không hiển thị
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Dừng auto scroll khi tab không hiển thị để tiết kiệm tài nguyên
                if (autoScrollInterval) {
                    clearInterval(autoScrollInterval);
                    autoScrollInterval = null;
                }
            } else {
                // Khôi phục auto scroll và snap khi quay lại tab
                resetAutoScroll();
                snapToNearestSlide(false);
            }
        });
    }

    function initNewestCarousel() {
        initCarousel('newest-books-carousel', 'newest-slides', 'newest-dot');
    }

    function initViewedCarousel() {
        initCarousel('viewed-books-carousel', 'viewed-slides', 'viewed-dot');
    }

    // Hàm chung để khởi tạo carousel với các tham số tùy chỉnh
    function initCarousel(carouselClass, slidesClass, dotClass) {
        const carousel = document.querySelector(`.${carouselClass}`);
        if (!carousel) return;

        const slidesContainer = carousel.querySelector(`.${slidesClass}`);
        const slides = carousel.querySelectorAll('.slide');
        const dots = carousel.querySelectorAll(`.${dotClass}`);

        if (!slidesContainer || slides.length === 0) return;

        let isScrolling;
        let isDown = false;
        let startX;
        let scrollLeft;
        let rafPending = false;
        let lastScrollPosition = 0;
        let lastTime = 0;
        let scrollVelocity = 0;
        let autoScrollInterval;
        let interacted = false;

        // Đảm bảo mỗi slide có đúng width 100%
        function updateSlideWidths() {
            const containerWidth = slidesContainer.clientWidth;
            slides.forEach(slide => {
                slide.style.width = `${containerWidth}px`;
            });
        }

        // Gọi hàm cập nhật kích thước ngay lập tức và khi cửa sổ thay đổi kích thước
        updateSlideWidths();

        // Sử dụng ResizeObserver để theo dõi thay đổi kích thước
        if (typeof ResizeObserver === 'function') {
            const resizeObserver = new ResizeObserver(debounce(() => {
                updateSlideWidths();
                requestAnimationFrame(() => {
                    snapToNearestSlide(false);
                    updateDots();
                });
            }, 200));

            resizeObserver.observe(slidesContainer);
            window.addEventListener('orientationchange', debounce(() => {
                updateSlideWidths();
                snapToNearestSlide(false);
            }, 200));
        } else {
            // Fallback cho trình duyệt không hỗ trợ ResizeObserver
            window.addEventListener('resize', debounce(() => {
                updateSlideWidths();
                snapToNearestSlide(false);
                updateDots();
            }, 200));
        }

        // Cập nhật dots dựa trên vị trí scroll
        function updateDots() {
            if (rafPending) return;

            rafPending = true;

            requestAnimationFrame(() => {
                const scrollPosition = slidesContainer.scrollLeft;
                const slideWidth = slides[0].offsetWidth;

                if (slideWidth === 0) {
                    rafPending = false;
                    return;
                }

                const currentIndex = Math.min(
                    Math.floor((scrollPosition + (slideWidth / 2)) / slideWidth),
                    slides.length - 1
                );

                // Cập nhật thanh tiến trình
                updateScrollProgressBar();

                // Chỉ thay đổi class khi cần thiết để tránh reflow/repaint
                dots.forEach((dot, index) => {
                    const shouldBeActive = index === currentIndex;
                    const isActive = dot.classList.contains('active');

                    if (shouldBeActive && !isActive) {
                        dot.classList.add('active', 'bg-blue-500');
                        dot.classList.remove('bg-gray-300');
                    } else if (!shouldBeActive && isActive) {
                        dot.classList.remove('active', 'bg-blue-500');
                        dot.classList.add('bg-gray-300');
                    }
                });

                rafPending = false;
            });
        }

        // Debounce hàm để tối ưu hiệu suất
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Throttle hàm scroll để giảm số lần gọi
        function throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        // Tính toán vận tốc cuộn
        function calculateVelocity(newPosition, newTime) {
            const deltaPosition = newPosition - lastScrollPosition;
            const deltaTime = newTime - lastTime;

            lastScrollPosition = newPosition;
            lastTime = newTime;

            // Tránh chia cho 0
            if (deltaTime === 0) return scrollVelocity;

            // Tính vận tốc (pixels/ms)
            return deltaPosition / deltaTime;
        }

        // Reset auto scroll interval
        function resetAutoScroll() {
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
            }

            if (!interacted) {
                autoScrollInterval = setInterval(() => {
                    const currentIndex = getCurrentSlideIndex();
                    const nextIndex = (currentIndex + 1) % slides.length;
                    scrollToSlide(nextIndex);
                }, 7000); // Thời gian dài hơn cho carousel thứ hai và ba
            }
        }

        // Lấy index của slide hiện tại
        function getCurrentSlideIndex() {
            const scrollPosition = slidesContainer.scrollLeft;
            const slideWidth = slides[0].offsetWidth;
            return Math.min(
                Math.floor((scrollPosition + (slideWidth / 2)) / slideWidth),
                slides.length - 1
            );
        }

        // Cuộn đến slide cụ thể
        function scrollToSlide(index, smooth = true) {
            const slideWidth = slidesContainer.clientWidth;
            const targetPosition = index * slideWidth;

            // Đảm bảo không scroll quá giới hạn
            const maxScrollLeft = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            const safePosition = Math.max(0, Math.min(targetPosition, maxScrollLeft));

            // Cài đặt behavior
            slidesContainer.style.scrollBehavior = smooth ? 'smooth' : 'auto';

            slidesContainer.scrollTo({
                left: safePosition,
                behavior: smooth ? 'smooth' : 'auto'
            });

            // Phục hồi scroll behavior
            setTimeout(() => {
                slidesContainer.style.scrollBehavior = 'smooth';
            }, 100);

            // Cập nhật dots sau khi cuộn
            setTimeout(updateDots, 300);
        }

        // Xử lý sự kiện pointerdown
        slidesContainer.addEventListener('pointerdown', (e) => {
            isDown = true;
            interacted = true; // Đánh dấu là đã tương tác
            slidesContainer.classList.add('dragging');
            startX = e.clientX;
            scrollLeft = slidesContainer.scrollLeft;
            lastTime = Date.now();
            lastScrollPosition = scrollLeft;

            // Dừng auto scroll
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }

            // Dừng animation đang diễn ra
            cancelAnimationFrame(isScrolling);

            // Ngăn chặn text selection khi kéo
            e.preventDefault();
        }, {
            passive: false
        });

        // Xử lý sự kiện pointermove hiệu quả hơn
        slidesContainer.addEventListener('pointermove', (e) => {
            if (!isDown) return;
            e.preventDefault();

            const x = e.clientX;
            const walk = (x - startX) * 1.5; // Hệ số kéo
            const newPosition = scrollLeft - walk;
            const newTime = Date.now();

            // Kiểm tra giới hạn
            const maxScroll = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            const boundedPosition = Math.max(0, Math.min(newPosition, maxScroll));

            // Cập nhật vị trí scroll với RAF để mượt hơn
            if (!rafPending) {
                rafPending = true;
                requestAnimationFrame(() => {
                    slidesContainer.scrollLeft = boundedPosition;
                    rafPending = false;
                });
            }

            // Tính toán vận tốc mới
            scrollVelocity = calculateVelocity(boundedPosition, newTime);
        }, {
            passive: false
        });

        // Momentum scrolling
        function momentumScroll() {
            // Giảm dần vận tốc theo đường cong mượt mà
            scrollVelocity *= 0.95;

            // Nếu vận tốc quá nhỏ, dừng lại và snap
            if (Math.abs(scrollVelocity) < 0.5) {
                snapToNearestSlide();
                return;
            }

            // Tính toán vị trí mới dựa trên vận tốc
            const newPosition = slidesContainer.scrollLeft + scrollVelocity * 10;

            // Kiểm tra giới hạn
            const maxScroll = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            if (newPosition <= 0 || newPosition >= maxScroll) {
                snapToNearestSlide();
                return;
            }

            // Cập nhật vị trí scroll với RAF để mượt hơn
            requestAnimationFrame(() => {
                slidesContainer.scrollLeft = newPosition;
                updateDots();
            });

            // Tiếp tục animation
            isScrolling = requestAnimationFrame(momentumScroll);
        }

        // Snap đến slide gần nhất
        function snapToNearestSlide(animate = true) {
            if (rafPending) return;

            const slideWidth = slidesContainer.clientWidth;
            const currentPosition = slidesContainer.scrollLeft;
            const targetIndex = Math.round(currentPosition / slideWidth);

            // Scroll đến slide gần nhất
            scrollToSlide(targetIndex, animate);

            // Reset auto scroll
            resetAutoScroll();
        }

        // Xử lý sự kiện khi kết thúc kéo
        function endDrag() {
            if (!isDown) return;

            isDown = false;
            slidesContainer.classList.remove('dragging');

            // Nếu vận tốc đủ lớn, thực hiện momentum scroll
            if (Math.abs(scrollVelocity) > 0.8) {
                isScrolling = requestAnimationFrame(momentumScroll);
            } else {
                // Nếu không, chỉ snap đến slide gần nhất
                snapToNearestSlide();
            }
        }

        slidesContainer.addEventListener('pointerup', endDrag);
        slidesContainer.addEventListener('pointerleave', endDrag);
        slidesContainer.addEventListener('pointercancel', endDrag);

        // Xử lý sự kiện cuộn thông thường
        slidesContainer.addEventListener('scroll', throttle(() => {
            if (isDown) return; // Không xử lý khi đang kéo

            // Đặt một timer để phát hiện khi nào việc cuộn kết thúc
            clearTimeout(isScrolling);
            isScrolling = setTimeout(() => {
                // Nếu đang không kéo và cuộn kết thúc, snap đến slide gần nhất
                if (!isDown) {
                    snapToNearestSlide();
                }
            }, 150);

            // Cập nhật dots trong khi cuộn
            if (!rafPending) {
                updateDots();
            }
        }, 100));

        // Cập nhật thanh tiến trình cuộn
        function updateScrollProgressBar() {
            const maxScroll = slidesContainer.scrollWidth - slidesContainer.clientWidth;
            if (maxScroll <= 0) return;

            const currentScroll = slidesContainer.scrollLeft;
            const progressPercent = (currentScroll / maxScroll) * 100;

            // Cập nhật width của thanh tiến trình
            carousel.style.setProperty('--scroll-progress', `${progressPercent}%`);

            // Đánh dấu rằng người dùng đã tương tác
            if (currentScroll > 0 && !slidesContainer.classList.contains('user-interacted')) {
                slidesContainer.classList.add('user-interacted');
                interacted = true;
            }
        }

        // Thêm sự kiện để ẩn gợi ý kéo lướt sau lần tương tác đầu tiên
        slidesContainer.addEventListener('pointerdown', () => {
            slidesContainer.classList.add('user-interacted');
            interacted = true;
        }, {
            once: true
        });

        // Xử lý sự kiện touch cho thiết bị di động tối ưu
        slidesContainer.addEventListener('touchstart', (e) => {
            isDown = true;
            interacted = true;
            slidesContainer.classList.add('dragging');
            startX = e.touches[0].clientX;
            scrollLeft = slidesContainer.scrollLeft;
            lastTime = Date.now();
            lastScrollPosition = scrollLeft;

            // Dừng auto scroll
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }

            // Dừng animation đang diễn ra
            cancelAnimationFrame(isScrolling);
        }, {
            passive: true
        });

        slidesContainer.addEventListener('touchmove', (e) => {
            if (!isDown) return;

            const x = e.touches[0].clientX;
            const walk = (x - startX) * 1.2;
            const newPosition = scrollLeft - walk;
            const newTime = Date.now();

            // Cập nhật vị trí scroll
            slidesContainer.scrollLeft = newPosition;

            // Tính toán vận tốc mới
            scrollVelocity = calculateVelocity(newPosition, newTime);
        }, {
            passive: true
        });

        slidesContainer.addEventListener('touchend', endDrag, {
            passive: true
        });

        // Cho phép nhấp vào dots để cuộn đến slide tương ứng
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                interacted = true;
                // Dừng bất kỳ momentum scrolling nào đang diễn ra
                cancelAnimationFrame(isScrolling);

                // Dừng auto scroll
                if (autoScrollInterval) {
                    clearInterval(autoScrollInterval);
                    autoScrollInterval = null;
                }

                // Scroll đến slide tương ứng
                scrollToSlide(index);
            });
        });

        // Thêm class để chỉ ra rằng có thể kéo được
        slidesContainer.classList.add('can-swipe');

        // Khởi tạo auto scroll
        resetAutoScroll();

        // Gọi updateDots lần đầu khi trang tải
        updateDots();

        // Tối ưu hóa khi tab không hiển thị
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Dừng auto scroll khi tab không hiển thị để tiết kiệm tài nguyên
                if (autoScrollInterval) {
                    clearInterval(autoScrollInterval);
                    autoScrollInterval = null;
                }
            } else {
                // Khôi phục auto scroll và snap khi quay lại tab
                resetAutoScroll();
                snapToNearestSlide(false);
            }
        });
    }
</script>
