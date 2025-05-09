<!-- Top Bar with Search -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6 filters-container">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <h2 class="text-lg font-bold text-gray-800 mb-4 md:mb-0">Bộ lọc sách</h2>
        @auth
            <button id="create-book-btn"
                class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                <i class="fas fa-plus mr-2"></i> Đăng sách
            </button>
        @else
            <a href="{{ route('front.login') }}"
                class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                <i class="fas fa-plus mr-2"></i> Đăng nhập để đăng sách
            </a>
        @endauth
    </div>

    <!-- Category Filters -->
    @php
        $visibleBookTypes = $booktypes->take(3);
        $hiddenBookTypes = $booktypes->slice(4);
    @endphp

    <div class="mt-4">
        <div class="flex flex-wrap gap-2 items-center">
            <a href="{{ route('front.book.index') }}"
                class="category-filter bg-gray-100 hover:bg-primary hover:text-white px-4 py-2 rounded-full text-sm transition {{ request()->routeIs('front.book.index') && !request()->has('book_types') ? 'active bg-primary text-white' : '' }}">
                <i class="fas fa-brain mr-1"></i> Tất cả
            </a>

            @foreach ($visibleBookTypes as $booktype)
                <a href="{{ route('front.book.byType', $booktype->slug) }}"
                    class="category-filter bg-gray-100 hover:bg-primary hover:text-white px-4 py-2 rounded-full text-sm transition {{ request()->is('front/book/type/' . $booktype->slug) ? 'active bg-primary text-white' : '' }}">
                    <i class="{{ $booktype->icon ?? 'fas fa-book' }} mr-1"></i> {{ $booktype->title }}
                </a>
            @endforeach

            @if ($hiddenBookTypes->isNotEmpty())
                <select onchange="if(this.value) window.location.href=this.value"
                    class="bg-gray-100 text-sm rounded-full px-4 py-2 border border-gray-300">
                    <option value="">Chọn thể loại khác</option>
                    @foreach ($hiddenBookTypes as $booktype)
                        <option value="{{ route('front.book.byType', $booktype->slug) }}"
                            {{ request()->is('front/book/type/' . $booktype->slug) ? 'selected' : '' }}>
                            {{ $booktype->title }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

</div>
