<!-- Right Sidebar -->

<div class="bg-white rounded-lg shadow-sm p-4">
    <!-- Hoạt động gần đây -->
    <div class="mb-6">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-bell mr-2 text-blue-500"></i>
            <span>Hoạt động gần đây</span>
        </h3>

        <div class="space-y-3">
            @if (isset($recentActivities) && count($recentActivities) > 0)
                @foreach ($recentActivities as $activity)
                    <div class="activity-item flex items-start p-2 rounded hover:bg-gray-50 transition-colors">
                        <div
                            class="{{ $activity->icon_bg ?? 'bg-blue-100' }} {{ $activity->icon_text ?? 'text-blue-500' }} rounded-full p-2 mr-3 flex-shrink-0">
                            <i class="{{ $activity->icon ?? 'fas fa-history' }} text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-800 break-words">{!! $activity->content !!}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500 text-sm">Không có hoạt động gần đây</p>
                </div>
            @endif
        </div>

        @if (isset($recentActivities) && count($recentActivities) > 0)
            <a href="#"
                class="block text-center text-blue-500 text-sm mt-4 hover:text-blue-700 transition-colors">
                Xem tất cả
            </a>
        @endif
    </div>

    <!-- Sách được xem nhiều -->
    <div class="mb-6">
        <h3 class="font-bold text-gray-800 mb-4">
            <a href="#books" class="tab-link flex items-center hover:text-blue-500 transition-colors" data-tab="books">
                <i class="fas fa-book mr-2 text-blue-500"></i>
                <span>Sách nổi bật</span>
            </a>
        </h3>

        <div class="space-y-3">
            @if (isset($books) && count($books) > 0)
                @php
                    $mostViewedBooks = $books->sortByDesc('views')->take(5);
                @endphp

                @if ($mostViewedBooks->count() > 0)
                    @foreach ($mostViewedBooks as $book)
                        <div class="flex items-start hover:bg-gray-50 p-2 rounded transition-colors">
                            <div class="flex-shrink-0 w-12 h-16 bg-gray-100 rounded overflow-hidden">
                                <a href="{{ route('front.book.show', $book->slug) }}">
                                    <img src="{{ $book->photo ? (strpos($book->photo, 'http') === 0 ? $book->photo : asset($book->photo)) : asset('images/default-book.jpg') }}"
                                        alt="{{ $book->title }}"
                                        class="w-full h-full object-cover transition-transform hover:scale-105">
                                </a>
                            </div>
                            <div class="ml-3 min-w-0 flex-1">
                                <h3 class="text-sm font-medium text-gray-800 truncate">
                                    <a href="{{ route('front.book.show', $book->slug) }}"
                                        class="hover:text-blue-500 transition-colors">
                                        {{ $book->title }}
                                    </a>
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-eye mr-1 text-blue-400"></i> {{ $book->views ?? 0 }} lượt xem
                                </p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <p class="text-gray-500 text-sm">Chưa có sách nào được xem</p>
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500 text-sm">Chưa có sách nào</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Bài viết được xem nhiều -->
    <div>
        <h3 class="font-bold text-gray-800 mb-4">
            <a href="#posts" class="tab-link flex items-center hover:text-blue-500 transition-colors" data-tab="posts">
                <i class="fas fa-newspaper mr-2 text-blue-500"></i>
                <span>Bài viết nổi bật</span>
            </a>
        </h3>

        <div class="space-y-3">
            @if (isset($userPosts) && count($userPosts) > 0)
                @php
                    $mostViewedPosts = collect($userPosts)->sortByDesc('hit')->take(5);
                @endphp

                @if (count($mostViewedPosts) > 0)
                    @foreach ($mostViewedPosts as $post)
                        <div class="flex items-start hover:bg-gray-50 p-2 rounded transition-colors">
                            <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                @php
                                    $images = json_decode($post->photo ?? '[]', true);
                                    $thumbnail_url = null;
                                    if (is_array($images) && count($images) > 0) {
                                        $thumbnail_url = $images[0];
                                    }
                                @endphp

                                <a href="{{ route('front.tblogs.show', $post->slug) }}">
                                    @if ($thumbnail_url)
                                        <img src="{{ $thumbnail_url }}" alt="{{ $post->title ?? 'Bài viết' }}"
                                            class="w-full h-full object-cover transition-transform hover:scale-105">
                                    @else
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-newspaper text-gray-400"></i>
                                        </div>
                                    @endif
                                </a>
                            </div>
                            <div class="ml-3 min-w-0 flex-1">
                                <h3 class="text-sm font-medium text-gray-800 truncate">
                                    <a href="{{ route('front.tblogs.show', $post->slug) }}"
                                        class="hover:text-blue-500 transition-colors">
                                        {{ $post->title ?? 'Bài viết không có tiêu đề' }}
                                    </a>
                                </h3>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <i class="fas fa-eye text-blue-400 mr-1"></i>
                                    <span>{{ $post->hit ?? 0 }} lượt xem</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <p class="text-gray-500 text-sm">Chưa có bài viết nào được xem</p>
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500 text-sm">Chưa có bài viết nào</p>
                </div>
            @endif
        </div>
    </div>
</div>


<!-- Right Sidebar for Mobile (Hiển thị dạng slide) -->
<div class="right-sidebar-mobile lg:hidden mt-6">
    <div class="bg-white rounded-lg shadow-sm p-4 overflow-x-auto">
        <div class="flex space-x-4 w-max">
            <!-- Hoạt động gần đây - Mobile -->
            <div class="w-64 flex-shrink-0">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-bell mr-2 text-blue-500"></i>
                    <span>Hoạt động gần đây</span>
                </h3>

                <div class="space-y-3">
                    @if (isset($recentActivities) && count($recentActivities) > 0)
                        <div class="flex items-start bg-gray-50 p-2 rounded">
                            <div
                                class="{{ $recentActivities[0]->icon_bg ?? 'bg-blue-100' }} {{ $recentActivities[0]->icon_text ?? 'text-blue-500' }} rounded-full p-2 mr-3 flex-shrink-0">
                                <i class="{{ $recentActivities[0]->icon ?? 'fas fa-history' }} text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-800 break-words">{!! $recentActivities[0]->content ?? 'Không có hoạt động gần đây' !!}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ isset($recentActivities[0]) ? \Carbon\Carbon::parse($recentActivities[0]->created_at)->diffForHumans() : '' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 text-sm">Không có hoạt động gần đây</p>
                        </div>
                    @endif
                </div>

                <!-- Thống kê lượt xem cho mobile -->
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Tổng lượt xem</h4>
                    <div class="flex justify-between items-center">
                        <div class="text-center flex-1 bg-blue-50 rounded-lg p-2 mr-2">
                            <span class="block text-blue-600 font-bold text-sm">{{ $totalBookViews ?? 0 }}</span>
                            <span class="text-xs text-gray-500">Sách</span>
                        </div>
                        <div class="text-center flex-1 bg-green-50 rounded-lg p-2">
                            <span class="block text-green-600 font-bold text-sm">{{ $totalBlogViews ?? 0 }}</span>
                            <span class="text-xs text-gray-500">Bài viết</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sách được xem nhiều - Mobile -->
            <div class="w-64 flex-shrink-0">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-book mr-2 text-blue-500"></i>
                    <span>Sách nổi bật</span>
                </h3>

                <div class="grid grid-cols-2 gap-3">
                    @if (isset($books) && count($books) > 0)
                        @php
                            $mostViewedBooks = $books->sortByDesc('views')->take(4);
                        @endphp

                        @if ($mostViewedBooks->count() > 0)
                            @foreach ($mostViewedBooks as $book)
                                <div class="bg-gray-50 rounded p-2">
                                    <div class="flex-shrink-0 w-full h-16 bg-gray-100 rounded overflow-hidden mb-2">
                                        <a href="{{ route('front.book.show', $book->slug) }}">
                                            <img src="{{ $book->photo ? (strpos($book->photo, 'http') === 0 ? $book->photo : asset($book->photo)) : asset('images/default-book.jpg') }}"
                                                alt="{{ $book->title }}" class="w-full h-full object-cover">
                                        </a>
                                    </div>
                                    <p class="text-xs text-center truncate">{{ $book->title }}</p>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center col-span-2 py-4">
                                <p class="text-gray-500 text-sm">Chưa có sách nào</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center col-span-2 py-4">
                            <p class="text-gray-500 text-sm">Chưa có sách nào</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
