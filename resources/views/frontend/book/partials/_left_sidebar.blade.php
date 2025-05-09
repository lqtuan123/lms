<!-- Left Sidebar -->
<aside id="left-sidebar" class="sidebar lg:w-1/5 lg:pr-4 mb-6 lg:mb-0">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <!-- Sidebar Toggle Button (Mobile) -->
        <button id="sidebar-toggle"
            class="lg:hidden w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md mb-4 flex items-center justify-between">
            <span>Bộ lọc sách</span>
            <i class="fas fa-bars"></i>
        </button>

        <!-- Category Filter -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-list-ul mr-2 text-primary"></i>
                Thể loại
            </h3>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('front.book.index') }}"
                        class="flex items-center text-gray-700 hover:text-primary {{ request()->routeIs('front.book.index') && !request()->has('book_types') ? 'text-primary font-medium' : '' }}">
                        <i class="fas fa-book mr-2 text-sm"></i>
                        Tất cả sách
                        <span
                            class="ml-auto bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">{{ \App\Modules\Book\Models\Book::count() }}</span>
                    </a>
                </li>
                @foreach ($booktypes as $type)
                    <li>
                        <a href="{{ route('front.book.byType', $type->slug) }}"
                            class="flex items-center text-gray-700 hover:text-primary {{ request()->is('front/book/type/' . $type->slug) ? 'text-primary font-medium' : '' }}">
                            <i class="{{ $type->icon ?? 'fas fa-book' }} mr-2 text-sm"></i>
                            {{ $type->title }}
                            <span
                                class="ml-auto bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $type->books_count ?? $type->books()->count() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Advanced Search -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-search-plus mr-2 text-primary"></i>
                Tìm kiếm nâng cao
            </h3>
            <form action="{{ route('frontend.book.advanced-search') }}" method="GET" class="space-y-3">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Tiêu đề sách</label>
                    <input type="text" name="book_title" value="{{ request('book_title') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Danh mục sách</label>
                    <select name="book_type_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Tất cả</option>
                        @foreach ($booktypes as $type)
                            <option value="{{ $type->id }}"
                                {{ request('book_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Mô tả</label>
                    <input type="text" name="summary" value="{{ request('summary') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                        placeholder="Tìm kiếm trong phần mô tả sách...">
                </div>
                <button type="submit"
                    class="w-full bg-primary text-white py-2 rounded-md text-sm hover:bg-blue-600 transition">
                    Áp dụng bộ lọc
                </button>
            </form>
        </div>

        <!-- Popular Tags -->
        <div>
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-tags mr-2 text-primary"></i>
                Tags phổ biến
            </h3>
            <div class="flex flex-wrap gap-2">
                @php
                    $popularTags = \App\Models\Tag::withCount('books')
                        ->orderBy('books_count', 'desc')
                        ->limit(10)
                        ->get();
                @endphp

                @foreach ($popularTags as $tag)
                    <a href="{{ route('front.book.search', ['tags[]' => $tag->id]) }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-xs">
                        {{ $tag->title }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</aside> 