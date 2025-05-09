{{-- Comments Section Template --}}
{{-- Parameters: comments, item_id, item_code, curuser --}}

<div class="comments-section">
    @if(isset($comments) && count($comments) > 0)
        @foreach($comments as $comment)
            @php
                $commentLikes = App\Models\CommentLike::where('comment_id', $comment->id)->count();
                $userLiked = Auth::check() ? App\Models\CommentLike::where('comment_id', $comment->id)->where('user_id', Auth::id())->exists() : false;
            @endphp
            <div class="comment mb-4 pb-2 border-b border-gray-100" id="comment-{{ $comment->id }}">
                <div class="flex">
                    <img src="{{ $comment->photo ? : asset('assets/images/placeholder.jpg') }}" alt="{{ $comment->full_name }}" class="w-9 h-9 rounded-full mr-3 object-cover">
                    <div class="flex-1">
                        <div class="bg-gray-100 rounded-lg p-3 relative">
                            <h4 class="font-medium text-gray-800">{{ $comment->full_name }}</h4>
                            <p class="text-gray-700">{!! $comment->content !!}</p>
                        </div>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <span>{{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}</span>
                            <span class="mx-1">·</span>
                            <button onclick="toggleReplyForm({{ $comment->id }})" class="hover:text-gray-700">Trả lời</button>
                            @if ($curuser && ($curuser->id == $comment->user_id || $curuser->role == 'admin'))
                                <span class="mx-1">·</span>
                                <button onclick="deleteComment({{ $comment->id }}, {{ $item_id }}, '{{ $item_code }}')" class="hover:text-red-500">
                                    Xóa
                                </button>
                                <span class="mx-1">·</span>
                                <button onclick="editComment({{ $comment->id }}, '{{ htmlspecialchars($comment->content, ENT_QUOTES) }}', {{ $item_id }}, '{{ $item_code }}')" class="hover:text-blue-500">
                                    Sửa
                                </button>
                            @endif
                            <span class="mx-1">·</span>
                            <button id="comment-like-{{ $comment->id }}" 
                                    class="comment-like-btn hover:text-gray-700 flex items-center" 
                                    data-comment-id="{{ $comment->id }}"
                                    data-item-id="{{ $item_id }}"
                                    data-item-code="{{ $item_code }}">
                                <i class="{{ $userLiked ? 'fas text-blue-500' : 'far' }} fa-thumbs-up mr-1"></i>
                                <span id="comment-like-count-{{ $comment->id }}">{{ $commentLikes }}</span>
                            </button>
                        </div>
                        
                        <!-- Reply Form -->
                        <div id="reply-form-{{ $comment->id }}" class="reply-form flex items-center mt-2 hidden">
                            <img src="{{ $curuser->photo ?? asset('assets/images/placeholder.jpg') }}" alt="User" class="w-7 h-7 rounded-full mr-2 object-cover">
                            <div class="relative flex-1">
                                <input type="text" id="reply-input-{{ $comment->id }}" placeholder="Viết câu trả lời..." class="reply-input w-full bg-gray-100 rounded-full px-3 py-1 text-sm focus:outline-none">
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                                    <button class="text-gray-400 hover:text-gray-600" 
                                            onclick="replyToComment({{ $comment->id }}, {{ $item_id }}, '{{ $item_code }}')" 
                                            data-parent-id="{{ $comment->id }}" 
                                            data-item-id="{{ $item_id }}" 
                                            data-item-code="{{ $item_code }}">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Replies -->
                        @if (isset($comment->subcomments) && count($comment->subcomments) > 0)
                            <div class="replies mt-2 pl-4 border-l border-gray-200">
                                @foreach ($comment->subcomments as $reply)
                                    @php
                                        $replyLikes = App\Models\CommentLike::where('comment_id', $reply->id)->count();
                                        $userLikedReply = Auth::check() ? App\Models\CommentLike::where('comment_id', $reply->id)->where('user_id', Auth::id())->exists() : false;
                                    @endphp
                                    <div class="reply mb-2" id="comment-{{ $reply->id }}">
                                        <div class="flex">
                                            <img src="{{ $reply->photo ? : asset('assets/images/placeholder.jpg') }}" alt="{{ $reply->full_name }}" class="w-7 h-7 rounded-full mr-2 object-cover">
                                            <div class="flex-1">
                                                <div class="bg-gray-100 rounded-lg p-2 relative">
                                                    <h4 class="font-medium text-gray-800 text-sm">{{ $reply->full_name }}</h4>
                                                    <p class="text-gray-700 text-sm">{!! $reply->content !!}</p>
                                                </div>
                                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                                    <span>{{ \Carbon\Carbon::parse($reply->created_at)->diffForHumans() }}</span>
                                                    <span class="mx-1">·</span>
                                                    <button 
                                                        class="reply-to-reply-btn hover:text-gray-700"
                                                        data-parent-id="{{ $comment->id }}"
                                                        data-reply-to-id="{{ $reply->id }}"
                                                        data-reply-to-name="{{ $reply->full_name }}"
                                                        data-item-id="{{ $item_id }}"
                                                        data-item-code="{{ $item_code }}"
                                                    >
                                                        Trả lời
                                                    </button>
                                                    @if ($curuser && ($curuser->id == $reply->user_id || $curuser->role == 'admin'))
                                                        <span class="mx-1">·</span>
                                                        <button onclick="deleteComment({{ $reply->id }}, {{ $item_id }}, '{{ $item_code }}')" class="hover:text-red-500">
                                                            Xóa
                                                        </button>
                                                        <span class="mx-1">·</span>
                                                        <button onclick="editComment({{ $reply->id }}, '{{ htmlspecialchars($reply->content, ENT_QUOTES) }}', {{ $item_id }}, '{{ $item_code }}')" class="hover:text-blue-500">
                                                            Sửa
                                                        </button>
                                                    @endif
                                                    <span class="mx-1">·</span>
                                                    <button id="comment-like-{{ $reply->id }}" 
                                                            class="comment-like-btn hover:text-gray-700 flex items-center" 
                                                            data-comment-id="{{ $reply->id }}"
                                                            data-item-id="{{ $item_id }}"
                                                            data-item-code="{{ $item_code }}">
                                                        <i class="{{ $userLikedReply ? 'fas text-blue-500' : 'far' }} fa-thumbs-up mr-1"></i>
                                                        <span id="comment-like-count-{{ $reply->id }}">{{ $replyLikes }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center text-gray-500 py-4">
            Chưa có bình luận nào. Hãy là người đầu tiên bình luận!
        </div>
    @endif
</div>

<script>
    // Initialize dropdowns after AJAX loads
    function initializeCommentDropdowns() {
        document.querySelectorAll('.comment-dropdown-toggle').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling;
                dropdown.classList.toggle('hidden');
            });
        });
        
        // Close comment dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.comment-dropdown-toggle + .dropdown-menu').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        });
    }
    
    // Edit comment function
    function editComment(commentId, content, itemId, itemCode) {
        // Create a modal for editing
        let modalId = 'edit-comment-modal';
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-4 w-full max-w-md">
                    <h3 class="text-lg font-medium mb-4">Chỉnh sửa bình luận</h3>
                    <textarea id="edit-comment-content" class="w-full border rounded p-2 mb-4" rows="4"></textarea>
                    <div class="flex justify-end">
                        <button id="cancel-edit" class="px-4 py-2 border rounded mr-2 hover:bg-gray-100">Hủy</button>
                        <button id="save-edit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Lưu</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Set up event listeners
            document.getElementById('cancel-edit').addEventListener('click', function() {
                modal.classList.add('hidden');
            });
        } else {
            modal.classList.remove('hidden');
        }
        
        // Set content and data attributes
        document.getElementById('edit-comment-content').value = content.replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        modal.dataset.commentId = commentId;
        modal.dataset.itemId = itemId;
        modal.dataset.itemCode = itemCode;
        
        // Update save handler
        document.getElementById('save-edit').onclick = function() {
            const newContent = document.getElementById('edit-comment-content').value.trim();
            if (!newContent) return;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken;
            
            fetch('/tcomments/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    id: commentId,
                    item_id: itemId,
                    item_code: itemCode,
                    content: newContent
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Update comment in DOM with the returned HTML
                    if (data.commentsHtml) {
                        document.getElementById('comments-container-' + itemId).innerHTML = data.commentsHtml;
                        initializeCommentDropdowns();
                    }
                    modal.classList.add('hidden');
                } else {
                    alert(data.msg || 'Không thể cập nhật bình luận');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi cập nhật bình luận');
            });
        };
    }
    
    // Initialize dropdowns when the comments are loaded
    initializeCommentDropdowns();

    // Đảm bảo CSRF token được cập nhật khi có sự kiện liên quan đến token
    document.addEventListener('DOMContentLoaded', function() {
        // Theo dõi các sự kiện click trên các nút like comment
        document.addEventListener('click', function(e) {
            if (e.target.closest('.comment-like-btn')) {
                console.log('Comment like button clicked');
                // Đảm bảo CSRF token đã được lưu
                window.lastCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            }
        });
    });
</script>
