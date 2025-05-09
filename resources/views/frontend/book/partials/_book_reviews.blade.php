<!-- Phần đánh giá và bình luận -->
<div class="bg-white rounded-lg shadow-md p-6 mt-8" id="reviews-section">
    <!-- Tab Menu -->
    <div class="book-tabs">
        <div class="book-tab active" data-tab="ratings">
            <span class="tab-icon">⭐</span> Đánh giá
            <span class="tab-count" id="rating-count">
                ({{ $book->rating_count ?? 0 }})
            </span>
        </div>
        <div class="book-tab" data-tab="comments">
            <span class="tab-icon">💬</span> Bình luận
            <span class="tab-count" id="comment-count">
                ({{ $book->comment_count ?? 0 }})
            </span>
        </div>
        <!-- Thêm tab tài liệu -->
        @php
            $hasResources = false;
            $resourceCount = 0;
            if (!empty($book->resources)) {
                if (is_string($book->resources)) {
                    $resourcesData = json_decode($book->resources, true);
                    $resourceCount = !empty($resourcesData['resource_ids'])
                        ? count($resourcesData['resource_ids'])
                        : 0;
                    $hasResources = $resourceCount > 0;
                } elseif (is_array($book->resources) && isset($book->resources['resource_ids'])) {
                    $resourceCount = count($book->resources['resource_ids']);
                    $hasResources = $resourceCount > 0;
                }
            }
        @endphp
        @if ($hasResources)
            <div class="book-tab" data-tab="resources">
                <span class="tab-icon">📁</span> Tài liệu
                <span class="tab-count" id="resources-count">
                    ({{ $resourceCount }})
                </span>
            </div>
        @endif
    </div>

    <!-- Tab Content: Đánh giá -->
    <div class="tab-content active" id="ratings-content">
        <!-- Thống kê đánh giá -->
        <div class="rating-stats">
            <div class="rating-average" id="rating-stats-average">{{ number_format($book->average_rating, 1) }}
            </div>
            <div class="rating-distribution" id="rating-distribution">
                @php
                    // Lấy phân bố đánh giá từ controller
                    $ratingService = app(\App\Services\RatingService::class);
                    $stats = $ratingService->getRatingStats($book->id);
                @endphp

                @for ($i = 5; $i >= 1; $i--)
                    <div class="rating-bar">
                        <div class="rating-bar-label">{{ $i }}</div>
                        <div class="rating-bar-track">
                            <div class="rating-bar-fill"
                                style="width: {{ $book->rating_count > 0 ? ($stats['distribution'][$i] / $book->rating_count) * 100 : 0 }}%">
                            </div>
                        </div>
                        <div class="rating-bar-count">{{ $stats['distribution'][$i] }}</div>
                        <div class="rating-bar-percent">
                            {{ $book->rating_count > 0 ? number_format(($stats['distribution'][$i] / $book->rating_count) * 100, 0) : 0 }}%
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Form đánh giá -->
        @auth
            <div class="rating-form" id="rating-form">
                <h4 class="rating-form-header">Viết đánh giá của bạn</h4>
                <label for="rating-stars-input" class="rating-form-label">Đánh giá của bạn:</label>
                <div class="rating-stars-input" id="rating-stars-input">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="rating-star cursor-pointer text-2xl" data-value="{{ $i }}">☆</span>
                    @endfor
                </div>
                <input type="hidden" id="rating-value" value="">
                <label for="rating-comment" class="rating-form-label">Nội dung đánh giá:</label>
                <textarea id="rating-comment" placeholder="Chia sẻ suy nghĩ của bạn về cuốn sách này..."></textarea>
                <div id="rating-error" class="text-red-500 text-sm mb-2 hidden"></div>
                <button id="submit-rating" class="rating-submit">Gửi đánh giá</button>
                <button id="delete-rating"
                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 hidden ml-2">Xóa đánh
                    giá</button>
            </div>
        @else
            <div class="bg-gray-50 p-4 rounded-md text-center">
                <p class="text-gray-600">Vui lòng <a href="{{ route('front.login') }}"
                        class="text-blue-600 hover:underline">đăng nhập</a> để đánh giá sách này.</p>
            </div>
        @endauth

        <!-- Danh sách đánh giá -->
        <div class="rating-list" id="rating-list">
            <!-- Đánh giá sẽ được tải bằng AJAX -->
            <div id="user-rating-loading" class="text-center py-2 text-gray-500 hidden">
                <p>Đang tải đánh giá của bạn...</p>
            </div>
            <div id="ratings-loading" class="text-center py-4 text-gray-500">
                <p>Đang tải đánh giá...</p>
            </div>
            <div id="ratings-container"></div>
            <div id="ratings-pagination" class="mt-4"></div>
        </div>
    </div>

    <!-- Tab Content: Bình luận -->
    <div class="tab-content" id="comments-content">
        <div class="flex justify-between border-b border-gray-100 pb-3 mb-3">

            <button onclick="toggleCommentBox({{ $book->id }}, 'book')"
                class="flex items-center justify-center w-1/3 py-1 text-gray-500 hover:bg-gray-100 rounded">
                <i class="far fa-comment mr-2"></i> Bình luận
            </button>

        </div>

        <div class="flex items-center">
            <img src="{{ auth()->user()->photo ?? 'https://randomuser.me/api/portraits/women/44.jpg' }}"
                alt="User" class="w-8 h-8 rounded-full object-cover mr-2">
            <div class="relative flex-1">
                <input type="text" id="comment-input-{{ $book->id }}" placeholder="Viết bình luận..."
                    class="comment-input w-full bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none">
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-1">
                    <button class="text-gray-400 hover:text-gray-600 emoji-trigger"
                        onclick="addEmoji({{ $book->id }}, event, 'book')"
                        data-item-id="{{ $book->id }}">
                        <i class="far fa-smile"></i>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600"
                        onclick="submitComment({{ $book->id }}, 'book')">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Comment Box - This div will be shown/hidden with toggleCommentBox() -->
        <div id="comment-box-{{ $book->id }}" class="comment-box bg-white rounded-lg shadow-sm p-4 mt-3"
            style="display: none;">
            <div id="comments-container-{{ $book->id }}" class="space-y-3">
                <!-- Comments will be loaded here dynamically -->
                <div class="text-center text-gray-500 text-sm py-2">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải bình luận...
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content: Tài liệu -->
    @if ($hasResources)
        <div class="tab-content" id="resources-content">
            <div class="resources-list">
                <h3 class="text-lg font-medium mb-4">Tài liệu đính kèm</h3>
                <div id="book-resources-container" class="grid gap-3">
                    <div class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500">
                        </div>
                        <p class="mt-2 text-gray-500">Đang tải danh sách tài liệu...</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 