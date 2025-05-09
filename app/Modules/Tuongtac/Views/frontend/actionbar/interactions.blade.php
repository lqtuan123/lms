{{-- Social Interactions Blade Template --}}
{{-- Parameters: reactions, userHasReacted, userReactionType, commentCount, shareCount --}}

<div class="social-interactions" data-item-code="{{ $item_code }}" id="item-{{ $item_id }}">
    <div class="flex items-center justify-between text-gray-500 border-t border-b border-gray-100 py-2 mb-3">
        <div class="flex items-center">
            <div class="flex items-center">
                <i class="fas fa-thumbs-up text-blue-500 mr-1"></i>
                <span class="text-xs" id="like-count-{{ $item_id }}">{{ $reactions['total'] ?? 0 }}</span>
            </div>
            <div class="flex items-center ml-4">
                <i class="fas fa-comment text-gray-400 mr-1"></i>
                <span class="text-xs">{{ $commentCount ?? 0 }}</span>
            </div>
        </div>
        <div class="text-xs share-count">{{ $shareCount ?? 0 }} lượt chia sẻ</div>
    </div>

    <div class="flex justify-between border-b border-gray-100 pb-3 mb-3">
        <button
            id="like-btn-{{ $item_id }}"
            onclick="reactToPost({{ $item_id }}, '{{ $item_code }}', 'Like')"
            class="flex items-center justify-center w-1/3 py-1 text-gray-500 hover:bg-gray-100 rounded {{ $userHasReacted ? 'text-blue-600' : '' }}"
            style="{{ $userReactionType && $userHasReacted ? 'color: #2078f4' : '' }}">
            @if($userHasReacted && $userReactionType)
                @switch($userReactionType)
                    @case('Love')
                        <span style="font-size:16px; margin-right:5px;">❤️</span> Love
                        @break
                    @case('Haha')
                        <span style="font-size:16px; margin-right:5px;">😆</span> Haha
                        @break
                    @case('Wow')
                        <span style="font-size:16px; margin-right:5px;">😮</span> Wow
                        @break
                    @case('Sad')
                        <span style="font-size:16px; margin-right:5px;">😢</span> Sad
                        @break
                    @case('Angry')
                        <span style="font-size:16px; margin-right:5px;">😠</span> Angry
                        @break
                    @default
                        <span style="font-size:16px; margin-right:5px;">👍</span> Like
                @endswitch
            @else
                <i class="far fa-thumbs-up mr-2"></i> Thích
            @endif
        </button>
        <button
            onclick="toggleCommentBox({{ $item_id }}, '{{ $item_code }}')"
            class="flex items-center justify-center w-1/3 py-1 text-gray-500 hover:bg-gray-100 rounded">
            <i class="far fa-comment mr-2"></i> Bình luận
        </button>
        <button
            onclick="sharePost({{ $item_id }}, '{{ $item_code }}', '{{ $slug ?? '' }}')"
            class="flex items-center justify-center w-1/3 py-1 text-gray-500 hover:bg-gray-100 rounded">
            <i class="fas fa-share mr-2"></i> Chia sẻ
        </button>
    </div>

    <div class="flex items-center">
        <img src="{{ auth()->user()->photo ?? 'https://randomuser.me/api/portraits/women/44.jpg' }}" alt="User"
            class="w-8 h-8 rounded-full object-cover mr-2">
        <div class="relative flex-1">
            <input type="text" id="comment-input-{{ $item_id }}" placeholder="Viết bình luận..."
                class="comment-input w-full bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none">
            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-1">
                <button class="text-gray-400 hover:text-gray-600 emoji-trigger" onclick="addEmoji({{ $item_id }})">
                    <i class="far fa-smile"></i>
                </button>
                <button class="text-gray-400 hover:text-gray-600" onclick="submitComment({{ $item_id }}, '{{ $item_code }}')">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Comment Box -->
    <div id="comment-box-{{ $item_id }}" class="comment-box bg-white rounded-lg shadow-sm p-4 mt-3"
        style="display: none;">
        <div id="comments-container-{{ $item_id }}" class="space-y-3">
            <!-- Comments will be loaded here dynamically -->
            <div class="text-center text-gray-500 text-sm py-2">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải bình luận...
            </div>
        </div>
    </div>
</div> 