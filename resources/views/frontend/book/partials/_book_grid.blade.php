<!-- Book Grid Section -->
<section class="mb-8">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 bg-gray-50 p-4 rounded-lg shadow-sm">
        <h2 class="text-xl font-bold text-gray-800 mb-3 sm:mb-0">
            @if (isset($bookType))
                {{ $bookType->title }}
            @else
                Tất cả sách
            @endif
        </h2>
        <div class="w-full sm:w-auto">
            <div class="flex flex-wrap justify-center sm:justify-end gap-2">
                <span class="text-sm text-gray-500 self-center mr-2 hidden sm:inline-block">Sắp xếp theo:</span>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" 
                    class="sort-btn {{ request('sort') == '' || request('sort') == 'latest' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} 
                            py-1.5 px-3 rounded-md text-sm font-medium transition-colors duration-200 flex items-center">
                    <i class="fas fa-clock mr-1.5"></i>Mới nhất
                </a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'views']) }}" 
                    class="sort-btn {{ request('sort') == 'views' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} 
                            py-1.5 px-3 rounded-md text-sm font-medium transition-colors duration-200 flex items-center">
                    <i class="fas fa-eye mr-1.5"></i>Lượt đọc
                </a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'title_asc']) }}" 
                    class="sort-btn {{ request('sort') == 'title_asc' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} 
                            py-1.5 px-3 rounded-md text-sm font-medium transition-colors duration-200 flex items-center">
                    <i class="fas fa-sort-alpha-down mr-1.5"></i>A-Z
                </a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'title_desc']) }}" 
                    class="sort-btn {{ request('sort') == 'title_desc' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} 
                            py-1.5 px-3 rounded-md text-sm font-medium transition-colors duration-200 flex items-center">
                    <i class="fas fa-sort-alpha-down-alt mr-1.5"></i>Z-A
                </a>
                <div class="ml-2 sm:ml-4 bg-blue-100 text-blue-800 py-1.5 px-3 rounded-md text-sm font-medium flex items-center">
                    <i class="fas fa-book mr-1.5"></i>{{ $books->total() }} sách
                </div>
            </div>
        </div>
    </div>

    <!-- Book List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($books as $book)
            <div class="book-card bg-white rounded-lg overflow-hidden shadow-sm transition cursor-pointer flex"
                data-book-id="{{ $book->id }}">
                <div class="relative h-36 w-24 flex-shrink-0">
                    <img src="{{ $book->photo ? $book->photo : asset('images/no-image.jpg') }}"
                        alt="{{ $book->title }}" class="h-full w-full object-cover">
                    <div
                        class="absolute top-1 right-1 bg-yellow-400 text-white text-xs font-bold px-1 py-0.5 rounded-full flex items-center">
                        <i class="fas fa-star mr-0.5 text-xs"></i>
                        {{ number_format($book->vote_average ?? 0, 1) }}
                    </div>
                    @if (isset($book->is_bookmarked) && $book->is_bookmarked)
                        <div class="absolute top-1 left-1 text-yellow-400 text-sm">
                            <i class="fas fa-bookmark"></i>
                        </div>
                    @endif
                </div>
                <div class="p-3 flex-grow flex flex-col">
                    <div class="flex justify-between items-start">
                        <h3 class="font-medium text-gray-800">{{ $book->title }}</h3>
                        <div class="text-xs text-gray-500 ml-2 flex-shrink-0">
                            <i class="fas fa-eye mr-1"></i> {{ $book->views ?? 0 }}
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">{{ $book->user->name ?? 'Unknown' }}</p>
                    <p class="text-sm text-gray-600 mt-1 mb-2 overflow-hidden line-clamp-2"
                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                        {{ $book->summary ?? 'Không có mô tả' }}
                    </p>
                    <div class="flex flex-wrap mt-auto">
                        <a href="{{ route('front.book.read', $book->id) }}"
                            class="text-primary hover:text-blue-700 text-sm bg-blue-50 px-3 py-1 rounded-md mr-2 mb-1">
                            <i class="fas fa-book-open mr-1"></i> Đọc
                        </a>
                        @if ($book->has_audio)
                            <a href="{{ route('front.book.show', ['id' => $book->slug, 'format' => 'audio']) }}"
                                class="text-green-500 hover:text-green-700 text-sm bg-green-50 px-3 py-1 rounded-md mr-2 mb-1">
                                <i class="fas fa-headphones mr-1"></i> Nghe
                            </a>
                        @endif
                        @php
                            $hasResources = false;
                            if (!empty($book->resources)) {
                                if (is_string($book->resources)) {
                                    $resourcesData = json_decode($book->resources, true);
                                    $hasResources = !empty($resourcesData['resource_ids']);
                                } else if (is_array($book->resources) && isset($book->resources['resource_ids'])) {
                                    $hasResources = !empty($book->resources['resource_ids']);
                                }
                            }
                        @endphp
                        @if ($hasResources)
                            <button type="button" 
                                class="download-resources-btn text-purple-500 hover:text-purple-700 text-sm bg-purple-50 px-3 py-1 rounded-md mr-2 mb-1"
                                data-id="{{ $book->id }}">
                                <i class="fas fa-download mr-1"></i> Tải
                            </button>
                        @endif
                        <button type="button" 
                            class="bookmark-btn text-{{ isset($book->is_bookmarked) && $book->is_bookmarked ? 'red' : 'gray' }}-500 hover:text-{{ isset($book->is_bookmarked) && $book->is_bookmarked ? 'red' : 'gray' }}-700 text-sm bg-{{ isset($book->is_bookmarked) && $book->is_bookmarked ? 'red' : 'gray' }}-50 px-3 py-1 rounded-md mr-2 mb-1"
                            data-id="{{ $book->id }}" 
                            data-code="book">
                            <i class="{{ isset($book->is_bookmarked) && $book->is_bookmarked ? 'fas' : 'far' }} fa-heart mr-1"></i> Thích
                        </button>
                        <a href="{{ route('front.book.show', $book->slug) }}"
                            class="text-gray-500 hover:text-gray-700 text-sm bg-gray-50 px-3 py-1 rounded-md mb-1">
                            <i class="fas fa-info-circle mr-1"></i> Chi tiết
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 col-span-2">
                <p class="text-gray-500">Không tìm thấy sách nào.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <nav class="flex items-center justify-between border-t border-gray-200 pt-6 mt-8">
        <div class="hidden sm:block">
            <p class="text-sm text-gray-700">
                Hiển thị
                <span class="font-medium">{{ $books->firstItem() ?? 0 }}</span>
                đến
                <span class="font-medium">{{ $books->lastItem() ?? 0 }}</span>
                của
                <span class="font-medium">{{ $books->total() }}</span> kết quả
            </p>
        </div>
        <div class="flex-1 flex justify-between sm:justify-end">
            {{ $books->appends(request()->except('page'))->links() }}
        </div>
    </nav>
</section> 