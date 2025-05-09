@extends('Tuongtac::frontend.blogs.body')
<?php

use Carbon\Carbon;
use App\Modules\Tuongtac\Models\TComment;
use App\Modules\Tuongtac\Models\TMotionItem;

// Tính số lượng bình luận trực tiếp trong view
$comment_count = TComment::where('item_id', $post->id)
    ->where('item_code', 'tblog')
    ->where('status', 'active')
    ->count();

// Lấy thông tin tương tác và tính tổng số lượt thích
$motionItem = TMotionItem::where('item_id', $post->id)
    ->where('item_code', 'tblog')
    ->first();
$likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;

// Cập nhật giá trị vào post nếu chưa có
if (!isset($post->comment_count)) {
    $post->comment_count = $comment_count;
}
if (!isset($post->likes_count)) {
    $post->likes_count = $likes_count;
}
?>
@section('topcss')
    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

    <style>
        .lightbox {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.9);
            text-align: center;
        }

        .lightbox-img {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }

        .next, .prev {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            background: rgba(0,0,0,0.5);
            padding: 16px;
            border: none;
            cursor: pointer;
        }

        .next { right: 10px; }
        .prev { left: 10px; }

        .dropzone {
            border: 2px dashed #0087F7;
            border-radius: 5px;
            background: white;
            min-height: 150px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .dropzone .dz-message {
            font-weight: 400;
            font-size: 16px;
            color: #646c9a;
        }

        .dropzone .dz-preview .dz-error-message {
            font-size: 12px;
        }

        .upload-status {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        
        /* Điều chỉnh phần hiển thị bài viết và bình luận */
        .post-container {
            display: block;
            width: 100%;
            max-width: 692.8px;
            margin: 0 auto;
        }
        
        .comment-section {
            width: 100%;
            margin-top: 20px;
            clear: both;
            display: block;
        }
        
        .comment-box {
            width: 100%;
        }
        
        /* Style cho các comment */
        .comment-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            width: 100%;
            display: block;
        }
        
        .comment-content {
            margin-top: 5px;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .lg\:flex-row {
                padding-right: 0.9rem !important;
                padding-left: 0.9rem !important;
            }
            
            /* Căn chỉnh rightSidebar */
            .right-sidebar {
                padding-right: 0.9rem !important;
            }
            
            /* Căn chỉnh container chứa nội dung chính */
            .main-container {
                padding-left: 0.9rem !important;
                padding-right: 0.9rem !important;
                max-width: 100%;
            }
        }
    </style>
@endsection

@section('inner-content')
    <?php
    $images = json_decode($post->photo, true); // Giải mã JSON thành mảng
    $json_photo = json_encode($images);
    
    // dd($images[0]);
    $createdAt = Carbon::parse($post->created_at); // Thay đổi $comment thành đối tượng bạn đang sử dụng
    $diffInMinutes = $createdAt->diffInMinutes();
    $diffInHours = $createdAt->diffInHours();
    $diffInDays = $createdAt->diffInDays();
    $thoigian = '';
    if ($diffInMinutes < 60) {
        $thoigian = $diffInMinutes . ' phút trước';
    } elseif ($diffInHours < 24) {
        $thoigian = $diffInHours . ' tiếng trước';
    } else {
        $thoigian = $diffInDays . ' ngày trước';
    }
    $adsense_code = '<ins class="adsbygoogle"
                style="display:block; text-align:center;"
                data-ad-layout="in-article"
                data-ad-format="fluid"
                data-ad-client="ca-pub-5437344106154965"
                data-ad-slot="3375673265"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>';
    
    $content = $post->content;
    // Tìm vị trí của thẻ <p> đầu tiên
    $position = strpos($content, '</p>', strlen($content) / 2); // Sau thẻ </p> gần giữa
    
    // Nếu tìm thấy vị trí, chèn mã AdSense
    if ($position !== false) {
        $new_content = substr_replace($content, $adsense_code, $position + 4, 0); // +4 vì thêm sau </p>
    } else {
        // Nếu không có <p>, chèn vào giữa
        $new_content = $content . $adsense_code;
    }
    
    ?>
    <div class="post-container">
        <!-- Bài viết chi tiết -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
            <!-- Nút quay lại -->
            <div class="px-4 pt-4">
                <a href="{{ url()->previous() }}" class="inline-flex items-center text-gray-600 hover:text-blue-600">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>

            <!-- Bài viết chi tiết -->
            <div class="post-card {{ $post->status == 0 ? 'bg-gray-50' : 'bg-white' }} p-4 relative" data-item-code="tblog">
                <!-- Nút hành động (edit, delete) -->
                <div class="absolute top-2 right-2 z-10 flex space-x-2">
                    @if (\Auth::id() == $post->user_id || (auth()->user() && auth()->user()->role == 'admin'))
                        <form action="{{ route('front.tblogs.destroy', $post->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                                    onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');" 
                                    title="Xóa bài viết">
                                <i class="fas fa-trash-alt text-gray-600 text-sm"></i>
                            </button>
                        </form>

                        <a href="{{ route('front.tblogs.edit', $post->id) }}" 
                           class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                           title="Chỉnh sửa bài viết">
                            <i class="fas fa-pencil-alt text-gray-600 text-sm"></i>
                        </a>

                        @if ($post->status == 1)
                            <a href="{{ route('front.tblogs.status', $post->id) }}" 
                               class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                               title="Ẩn bài viết">
                                <i class="fas fa-eye-slash text-gray-600 text-sm"></i>
                            </a>
                        @else
                            <a href="{{ route('front.tblogs.status', $post->id) }}" 
                               class="w-7 h-7 bg-white rounded-full shadow-sm flex items-center justify-center hover:bg-gray-100 transition-colors"
                               title="Hiển thị bài viết">
                                <i class="fas fa-eye text-gray-600 text-sm"></i>
                            </a>
                        @endif
                    @endif
                </div>

                <div class="post-author flex items-center mb-3">
                    <a href="{{ route('front.user.profile', ['id' => $post->author ? $post->author->id : 0]) }}">
                        <img src="{{ $post->author ? $post->author->photo : '/images/default-avatar.png' }}" alt="Author avatar"
                            class="w-12 h-12 object-cover rounded-full mr-3">
                    </a>
                    <div>
                        <a href="{{ route('front.user.profile', ['id' => $post->author ? $post->author->id : 0]) }}" class="hover:text-blue-600">
                            <h3 class="font-medium">{{ $post->author ? $post->author->full_name : 'Người dùng không xác định' }}</h3>
                        </a>
                        <p class="text-gray-500 text-sm">
                            <i class="far fa-calendar-alt mr-1"></i> {{ date('d/m/Y', strtotime($post->created_at)) }}
                        </p>
                    </div>
                </div>
                
                <!-- Nội dung bài viết -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800 mb-3">{{ $post->title }}</h1>
                    
                    @if (count($post->tags) > 0)
                        <div class="flex flex-wrap gap-2 mb-4">
                    @foreach ($post->tags as $tag)
                                <a href="{{ route('front.tblogs.tag', $tag->slug) }}"
                                    class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs hover:bg-blue-200">
                                    #{{ $tag->title }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- Hình ảnh bài viết -->
                    @if ($images && count($images) > 0)
                        <!-- Hiển thị ảnh đầu tiên lớn -->
                        <div class="mb-4">
                            <img src="{{ $images[0] }}" alt="{{ $post->title }}" 
                                class="w-full h-auto rounded-lg cursor-pointer" 
                                onclick="openLightbox('{{ $json_photo }}', 0)">
                        </div>
                        
                        <!-- Hiển thị các ảnh khác nhỏ hơn nếu có -->
                        @if (count($images) > 1)
                            <div class="flex space-x-2 mb-4 overflow-x-auto pb-2">
                                @foreach ($images as $index => $image)
                                    @if ($index > 0 && $image)
                                        <img src="{{ $image }}" alt="{{ $post->title }}" 
                                            class="w-20 h-20 object-cover rounded cursor-pointer" 
                                            onclick="openLightbox('{{ $json_photo }}', {{ $index }})">
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <!-- Nội dung bài viết -->
                    <div class="prose max-w-none text-gray-800">
                        {!! $new_content !!}
                    </div>
                    
                    <!-- URL tài liệu nếu có -->
                    @if(isset($url) && $url)
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center">
                                <i class="fas fa-link text-blue-500 mr-2"></i>
                                <a href="{{ $url }}" class="text-blue-600 hover:underline break-all" target="_blank">
                                    {{ $url }}
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Hiển thị các tài liệu đính kèm từ resources -->
                    @php
                        $resourceIds = is_array($post->resources) ? $post->resources : json_decode($post->resources, true);
                        if (is_array($resourceIds) && !empty($resourceIds)) {
                            // Đảm bảo $resourceIds là mảng phẳng (không lồng nhau) trước khi truyền vào whereIn
                            $flatResourceIds = [];
                            foreach ($resourceIds as $id) {
                                if (is_array($id)) {
                                    // Nếu là mảng lồng nhau, thêm từng phần tử vào mảng phẳng
                                    foreach ($id as $subId) {
                                        if (is_numeric($subId)) {
                                            $flatResourceIds[] = $subId;
                                        }
                                    }
                                } elseif (is_numeric($id)) {
                                    // Nếu là giá trị số, thêm trực tiếp vào mảng phẳng
                                    $flatResourceIds[] = $id;
                                }
                            }
                            $resources = \App\Modules\Resource\Models\Resource::whereIn('id', $flatResourceIds)->get();
                        } else {
                            $resources = collect([]);
                        }
                    @endphp
                    
                    @if($resources->count() > 0)
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold mb-2">Tài liệu đính kèm</h3>
                            <div class="space-y-2">
                                @foreach($resources as $resource)
                                    @if($resource->link_code == 'file' && $resource->file_name != 'file.unknown' && $resource->file_name != 'unknown_file')
                                        <!-- Nếu là file thật sự (được tải lên) -->
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                                    <span class="text-gray-700">{{ $resource->file_name ?: basename($resource->url) }}</span>
                                                </div>
                                                <button type="button" 
                                                    class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm download-file-btn w-24 text-center"
                                                    data-resource-id="{{ $resource->id }}">
                                                    <i class="fas fa-download mr-1"></i> Tải về
                                                </button>
                                            </div>
                                        </div>
                                    @elseif($resource->link_code == 'url' && strpos($resource->url, 'youtube.com') !== false || strpos($resource->url, 'youtu.be') !== false)
                                        <!-- Nếu là YouTube video, hiển thị nhúng -->
                                        @php
                                            $youtubeId = '';
                                            // Lấy ID từ URL YouTube
                                            if (strpos($resource->url, 'youtube.com/watch') !== false) {
                                                parse_str(parse_url($resource->url, PHP_URL_QUERY), $params);
                                                $youtubeId = $params['v'] ?? '';
                                            } elseif (strpos($resource->url, 'youtu.be') !== false) {
                                                $youtubeId = substr(parse_url($resource->url, PHP_URL_PATH), 1);
                                            }
                                        @endphp
                                        
                                        @if($youtubeId)
                                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div class="mb-2">
                                                    <i class="fab fa-youtube text-red-500 mr-2"></i>
                                                    <span class="text-gray-700">{{ $resource->title ?: 'YouTube Video' }}</span>
                                                </div>
                                                <div class="relative" style="padding-bottom: 56.25%;">
                                                    <iframe 
                                                        class="absolute top-0 left-0 w-full h-full rounded-md"
                                                        src="https://www.youtube.com/embed/{{ $youtubeId }}" 
                                                        frameborder="0" 
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                        allowfullscreen>
                                                    </iframe>
                                                </div>
                                            </div>
                                        @else
                                            <!-- Fallback nếu không lấy được ID -->
                                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div class="flex items-center">
                                                    <i class="fab fa-youtube text-red-500 mr-2"></i>
                                                    <a href="{{ $resource->url }}" class="text-blue-600 hover:underline break-all" target="_blank">
                                                        {{ $resource->title ?: $resource->url }}
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($resource->link_code == 'url' && filter_var($resource->url, FILTER_VALIDATE_URL) && $resource->url != '#' && strpos($resource->url, '/storage/uploads/resources/') === false)
                                        <!-- Nếu là URL thông thường và là URL hợp lệ, và không trỏ đến resource của hệ thống -->
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <div class="flex items-center">
                                                <i class="fas fa-link text-blue-500 mr-2"></i>
                                                <a href="{{ $resource->url }}" class="text-blue-600 hover:underline break-all" target="_blank">
                                                    {{ $resource->title ?: $resource->url }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Thống kê tương tác -->
                <div class="flex items-center justify-between text-gray-500 border-t border-b border-gray-100 py-2 mb-3">
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <i class="fas fa-thumbs-up text-blue-500 mr-1"></i>
                            <span class="text-xs" id="like-count-{{ $post->id }}">{{ $post->likes_count ?? 0 }}</span>
                        </div>
                        <div class="flex items-center ml-4">
                            <i class="fas fa-comment text-gray-400 mr-1"></i>
                            <span class="text-xs">{{ $post->comment_count ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="text-xs">{{ $post->share_count ?? 0 }} lượt chia sẻ</div>
                </div>

                <!-- Nút tương tác -->
                <div class="flex justify-between pb-3 mb-3">
                    <button id="like-btn-{{ $post->id }}"
                        class="like-button flex items-center justify-center w-1/4 py-1 text-gray-500 hover:bg-gray-100 rounded {{ isset($post->user_has_liked) && $post->user_has_liked ? 'text-blue-600' : '' }}"
                        data-item-id="{{ $post->id }}" data-item-code="tblog">
                        <i class="{{ isset($post->user_has_liked) && $post->user_has_liked ? 'fas' : 'far' }} fa-thumbs-up mr-2"></i>
                        Thích
                    </button>
                    <button onclick="toggleCommentBox({{ $post->id }}, 'tblog')"
                        class="flex items-center justify-center w-1/4 py-1 text-gray-500 hover:bg-gray-100 rounded">
                        <i class="far fa-comment mr-2"></i> Bình luận
                    </button>
                    <button onclick="sharePost({{ $post->id }}, '{{ $post->slug }}', 'tblog')"
                        class="flex items-center justify-center w-1/4 py-1 text-gray-500 hover:bg-gray-100 rounded">
                        <i class="fas fa-share mr-2"></i> Chia sẻ
                    </button>
                    <button id="bookmark-btn-{{ $post->id }}" onclick="toggleBookmark({{ $post->id }}, 'tblog')"
                        class="flex items-center justify-center w-1/4 py-1 text-gray-500 hover:bg-gray-100 rounded {{ isset($post->is_bookmarked) && $post->is_bookmarked ? 'text-red-500' : '' }}">
                        <i class="{{ isset($post->is_bookmarked) && $post->is_bookmarked ? 'fas' : 'far' }} fa-heart mr-2"></i>
                        Yêu thích
                    </button>
                </div>

                <!-- Hộp nhập bình luận -->
                <div class="mt-3">
                    <div class="flex items-center">
                        <img src="{{ auth()->user()->photo ?? 'https://randomuser.me/api/portraits/women/44.jpg' }}"
                            alt="User" class="w-8 h-8 rounded-full object-cover mr-2">
                        <div class="relative flex-1">
                            <input type="text" id="comment-input-{{ $post->id }}"
                                placeholder="Viết bình luận..."
                                class="comment-input w-full bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none">
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-1">
                                <button class="text-gray-400 hover:text-gray-600 emoji-trigger"
                                    onclick="addEmoji({{ $post->id }})">
                                    <i class="far fa-smile"></i>
                                </button>
                                <button class="text-gray-400 hover:text-gray-600"
                                    onclick="submitComment({{ $post->id }}, 'tblog')">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần bình luận -->
        <div class="comment-section bg-white rounded-lg shadow-sm p-4 mb-6">
            <h3 class="text-lg font-semibold mb-4">Bình luận</h3>
            <div id="comment-box-{{ $post->id }}" class="comment-box">
                <div id="comments-container-{{ $post->id }}" class="space-y-3">
                    <!-- Comments will be loaded here dynamically -->
                    <div class="text-center text-gray-500 text-sm py-2">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải bình luận...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox cho ảnh lớn -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="close" onclick="closeLightbox()">&times;</span>
        <img id="lightbox-img" class="lightbox-img" onclick="event.stopPropagation()" src="">
        <button class="next" onclick="event.stopPropagation(); changeImage(1);">&#10095;</button>
        <button class="prev" onclick="event.stopPropagation(); changeImage(-1);">&#10094;</button>
    </div>

    <!-- Thêm modal popup cho chỉnh sửa bài viết -->
    <div id="editPostModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-4 border-b">
                <h2 class="text-xl font-bold">Chỉnh sửa bài viết</h2>
                <button onclick="closeEditPostModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="editPostContent" class="p-6">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('botscript')
    <!-- Dropzone JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    
    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    
    <!-- CKEditor -->
    <script src="{{ asset('js/js/ckeditor.js') }}"></script>

    <script>
        // Toggle bookmark và cập nhật UI
        function toggleBookmark(postId, itemCode) {
            fetch('{{ route('front.tblog.bookmark') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    item_id: postId,
                    item_code: itemCode
                })
            })
            .then(response => response.json())
            .then(data => {
                // Cập nhật UI
                const bookmarkBtn = document.getElementById(`bookmark-btn-${postId}`);
                const bookmarkIcon = bookmarkBtn.querySelector('i');
                
                if (data.status === 'added') {
                    bookmarkBtn.classList.add('text-red-500');
                    bookmarkIcon.classList.remove('far');
                    bookmarkIcon.classList.add('fas');
                } else {
                    bookmarkBtn.classList.remove('text-red-500');
                    bookmarkIcon.classList.remove('fas');
                    bookmarkIcon.classList.add('far');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Đã xảy ra lỗi khi lưu bài viết. Vui lòng thử lại sau.');
            });
        }
        
        // Tải comments khi trang được load
        document.addEventListener('DOMContentLoaded', function() {
            // Load comments
            loadComments({{ $post->id }}, 'tblog');
            
            // Xử lý nút tải xuống tài liệu
            document.querySelectorAll('.download-file-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const resourceId = this.getAttribute('data-resource-id');
                    if (!resourceId) return;
                    
                    // Tạo form ẩn để gửi request POST
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("resource.download") }}';
                    form.style.display = 'none';
                    
                    // Thêm CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);
                    
                    // Thêm resource_id
                    const resourceIdInput = document.createElement('input');
                    resourceIdInput.type = 'hidden';
                    resourceIdInput.name = 'resource_id';
                    resourceIdInput.value = resourceId;
                    form.appendChild(resourceIdInput);
                    
                    // Thêm form vào body, submit và sau đó xóa
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                });
            });
        });
        
        // Xử lý lightbox
        let currentImageIndex = 0;
        let images = [];

        function openLightbox(imageList, index) {
            images = JSON.parse(imageList);
            currentImageIndex = index;
            document.getElementById("lightbox-img").src = images[currentImageIndex];
            document.getElementById("lightbox").style.display = "flex";
        }

        function closeLightbox() {
            document.getElementById("lightbox").style.display = "none";
        }

        function changeImage(direction) {
            currentImageIndex += direction;
            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1;
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0;
            }
            document.getElementById("lightbox-img").src = images[currentImageIndex];
        }
        
        // Mở popup chỉnh sửa bài viết
        function openEditPostModal(postId) {
            const modal = document.getElementById('editPostModal');
            const content = document.getElementById('editPostContent');

            // Show modal with loading spinner
            modal.classList.remove('hidden');
            content.innerHTML =
                '<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div></div>';

            console.log(`Đang gửi request tới URL: /tblogs/${postId}/edit`);
            
            // Fetch form content from server với X-Requested-With header để đánh dấu Ajax request
            fetch(`/tblogs/${postId}/edit`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin' // Đảm bảo gửi cookies để xác thực session
                })
                .then(response => {
                    console.log('Nhận phản hồi:', response.status, response.statusText);
                    
                    if (!response.ok) {
                        if (response.status === 401) {
                            window.location.href = '{{ route('front.login') }}';
                            throw new Error('Bạn cần đăng nhập để thực hiện chức năng này.');
                        }
                        if (response.status === 404) {
                            throw new Error('Không tìm thấy bài viết hoặc API không tồn tại. Vui lòng kiểm tra lại đường dẫn.');
                        }
                        return response.text().then(text => {
                            console.error('Nội dung phản hồi lỗi:', text);
                            try {
                                const json = JSON.parse(text);
                                throw new Error(json.error || 'Không thể tải form.');
                            } catch (e) {
                                throw new Error('Không thể tải form. Lỗi server: ' + response.status);
                            }
                        });
                    }
                    
                    // Kiểm tra xem phản hồi có phải là JSON không
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Phản hồi không phải JSON:', contentType);
                        throw new Error('Phản hồi không hợp lệ từ server, không đúng định dạng JSON');
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('Dữ liệu thành công:', data);
                    // Render form
                    renderEditForm(data, content, postId);
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    content.innerHTML = `
                        <div class="text-red-500 text-center p-4">
                            <p class="font-bold text-lg mb-2">Không thể tải form</p>
                            <p>${error.message}</p>
                            <div class="mt-4">
                                <button onclick="closeEditPostModal()" class="px-4 py-2 bg-gray-300 rounded-md">Đóng</button>
                                <a href="/tblogs/${postId}/edit" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md">Mở trang chỉnh sửa</a>
                            </div>
                        </div>`;
                });
        }

        function closeEditPostModal() {
            const modal = document.getElementById('editPostModal');
            modal.classList.add('hidden');
        }
        
        // Function để hiện thị form chỉnh sửa
        function renderEditForm(data, contentElement, postId) {
            // Chuẩn bị dữ liệu ảnh từ post
            let imagesHTML = '';
            try {
                const images = JSON.parse(data.post.photo);
                if (images && images.length > 0) {
                    imagesHTML = `
                    <div class="flex mt-2">
                        ${images.map(photo => photo ? `
                        <div class="image-preview mr-2">
                            <div class="product_photo relative">
                                <img class="rounded-md" style="width:50px; height:50px" src="${photo}">
                            </div>
                            <div title="Xóa hình này?" data-photo="${photo}" class="dlt_btn absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center cursor-pointer">x</div>
                        </div>
                        ` : '').join('')}
                    </div>`;
                }
            } catch (e) {
                console.error('Lỗi xử lý ảnh:', e);
            }
            
            // Tạo form HTML
            const formHtml = `
                <div class="p-4">
                    <form id="editPostForm" action="/tblogs/${postId}" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="${data.csrf_token}">
                        <input type="hidden" name="_method" value="PATCH">
                        
                        <!-- Upload ảnh đầu bài -->
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Upload hình ảnh</label>
                            <div class="dropzone border-2 border-dashed border-blue-400 rounded-lg p-4 bg-gray-50" id="imageDropzone"></div>
                            <div id="uploadStatus" class="mt-2 p-2 hidden"></div>
                        </div>
                        
                        <!-- Hiển thị ảnh đã tải lên trước đó -->
                        ${imagesHTML}
                        
                        <!-- Ẩn input để lưu tên file ảnh -->
                        <input type="hidden" name="photo" id="uploadedimages" value='${data.post.photo || "[]"}'>
                        
                        <!-- Tiêu đề bài viết -->
                        <div class="mb-4 mt-4">
                            <label class="block text-gray-700 mb-2">Tiêu đề</label>
                            <input type="text" name="title" value="${data.post.title}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <!-- Thẻ bài viết -->
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Tags</label>
                            <select id="edit-tags" name="tags[]" multiple class="w-full">
                                ${data.tags.map(tag => {
                                    const isSelected = data.post.tags && data.post.tags.some(t => t.id === tag.id);
                                    return `<option value="${tag.id}" ${isSelected ? 'selected' : ''}>${tag.title}</option>`;
                                }).join('')}
                            </select>
                            <span class="text-sm text-gray-500">Tối đa 5 tag</span>
                        </div>
                        
                        <!-- Nội dung bài viết -->
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Nội dung</label>
                            <textarea name="content" id="edit-content" rows="10" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">${data.post.content}</textarea>
                        </div>
                        
                        <div class="flex justify-end mt-4">
                            <button type="button" onclick="closeEditPostModal()" 
                                class="px-4 py-2 bg-gray-300 rounded-md mr-2">Hủy</button>
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-md">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            `;
            
            // Gán HTML vào element
            contentElement.innerHTML = formHtml;
            
            // Khởi tạo Dropzone
            Dropzone.autoDiscover = false;
            const uploadedimages = [];
            const uploadStatus = document.getElementById('uploadStatus');

            // Khởi tạo từ dữ liệu hiện có
            try {
                if (data.post.photo) {
                    const images = JSON.parse(data.post.photo);
                    if (Array.isArray(images)) {
                        images.forEach(img => {
                            if (img) uploadedimages.push(img);
                        });
                    }
                }
            } catch (e) {
                console.error('Lỗi khi phân tích JSON ảnh:', e);
            }
            
            // Xử lý Dropzone
            const imageDropzone = new Dropzone("#imageDropzone", {
                url: "{{ route('front.upload.avatar') }}",
                paramName: "photo",
                maxFilesize: 2,
                acceptedFiles: 'image/*',
                addRemoveLinks: true,
                dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
                dictRemoveFile: "Xóa ảnh",
                thumbnailWidth: 150,
                thumbnailHeight: 150,
                maxFiles: 5,
                headers: {
                    'X-CSRF-TOKEN': data.csrf_token
                },
                success: function(file, response) {
                    uploadedimages.push(response.link);
                    document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
                },
                removedfile: function(file) {
                    try {
                        if (file.xhr && file.xhr.response) {
                            const response = JSON.parse(file.xhr.response);
                            const index = uploadedimages.indexOf(response.link);
                            if (index !== -1) {
                                uploadedimages.splice(index, 1);
                                document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
                            }
                        }
                    } catch (e) {
                        console.error("Lỗi khi xóa file:", e);
                    }
                    file.previewElement.remove();
                }
            });
            
            // Khởi tạo Tom Select cho tags
            new TomSelect('#edit-tags', {
                maxItems: 5,
                plugins: ['remove_button'],
                placeholder: 'Chọn thẻ...',
                create: false
            });
            
            // Xử lý nút xóa ảnh hiện có
            document.querySelectorAll('.dlt_btn').forEach(button => {
                button.addEventListener('click', function() {
                    const photo = this.getAttribute('data-photo');
                    const parent = this.closest('.image-preview');
                    
                    if (parent) {
                        parent.remove();
                    }
                    
                    const index = uploadedimages.indexOf(photo);
                    if (index !== -1) {
                        uploadedimages.splice(index, 1);
                        document.getElementById('uploadedimages').value = JSON.stringify(uploadedimages);
                    }
                });
            });
            
            // Khởi tạo CKEditor
            if (typeof ClassicEditor !== 'undefined') {
                ClassicEditor.create(document.getElementById('edit-content'), {
                    ckfinder: {
                        uploadUrl: '{{route("upload.ckeditor")."?_token=".csrf_token()}}'
                    }
                }).catch(error => {
                    console.error('CKEditor error:', error);
                });
            }
            
            // Xử lý submit form
            const form = document.getElementById('editPostForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return {};
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        // Đóng modal và reload trang để thấy thay đổi
                        closeEditPostModal();
                        window.location.reload();
                    } else if (data && data.error) {
                        alert('Có lỗi xảy ra: ' + data.error);
                    } else {
                        closeEditPostModal();
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật bài viết');
                });
            });
        }
    </script>
@endsection



