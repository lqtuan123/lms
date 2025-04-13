@extends('Tuongtac::frontend.blogs.body')

@section('topcss')
<link rel="stylesheet" href="{{ asset('frontend/assets_f/custom-group.css') }}">
@endsection

@section('inner-content')
<div class="container">

    <div class="group-banner">
        <img src="{{ $group->photo ? asset($group->photo) : asset('images/default-group-banner.jpg') }}" alt="{{ $group->title }}">
    </div>
    
    <div class="group-header">
        <div class="group-avatar">
            <img src="{{ $group->photo ? asset($group->photo) : asset('images/default-group-avatar.jpg') }}" alt="{{ $group->title }}">
        </div>
        
        <div class="group-info">
            <h1 class="group-title">{{ $group->title }}</h1>
            <div class="group-type">
                @if($group->type == 'private')
                <span class="badge bg-warning">Nhóm riêng tư</span>
                @else
                <span class="badge bg-success">Nhóm công khai</span>
                @endif
                <span>Được tạo bởi: {{ $group->author ? $group->author->full_name : 'Người dùng không xác định' }}</span>
            </div>
            
            <div class="group-stats">
                <div class="group-stat">
                    <i class="fas fa-users"></i>
                    <span>{{ is_array($group->members) ? count($group->members) : (is_string($group->members) ? count(json_decode($group->members, true) ?: []) : 0) }} thành viên</span>
                </div>
                <div class="group-stat">
                    <i class="fas fa-file-alt"></i>
                    <span>{{ isset($group->posts) && is_array($group->posts) ? count($group->posts) : 0 }} bài đăng</span>
                </div>
                <div class="group-stat">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Tạo ngày: {{ date('d/m/Y', strtotime($group->created_at)) }}</span>
                </div>
            </div>
            
            <div class="group-description mt-3">
                <h5>Giới thiệu nhóm</h5>
                <p>{{ $group->description }}</p>
            </div>
            
            <div class="group-actions">
                @if(Auth::check())
                    @if($isMember)
                        <form action="{{ route('group.leave', $group->id) }}" method="POST" class="me-2 d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt"></i> Rời nhóm
                            </button>
                        </form>
                        
                        <a href="{{ route('front.tblogs.create', ['group_id' => $group->id]) }}" class="btn btn-outline-primary d-inline-block">
                            <i class="fas fa-pen"></i> Đăng bài
                        </a>
                    @else
                        @if($group->type == 'public' || $joinRequest)
                            <form action="{{ route('group.join', $group->id) }}" method="POST">
                                @csrf
                                @if($joinRequest && $joinRequest->status == 'pending')
                                    <button type="button" class="btn btn-outline-warning" disabled>
                                        <i class="fas fa-clock"></i> Đang chờ duyệt
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-sign-in-alt"></i> Tham gia nhóm
                                    </button>
                                @endif
                            </form>
                        @endif
                    @endif
                    
                    @if(Auth::id() == $group->author_id)
                        <a href="{{ route('group.edit', $group->id) }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-edit"></i> Chỉnh sửa nhóm
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            @if($isMember || $group->is_private == 0)
                <ul class="nav nav-tabs mb-3" id="groupTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts" type="button" role="tab" aria-controls="posts" aria-selected="true">
                            Bài đăng
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab" aria-controls="members" aria-selected="false">
                            Thành viên
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab" aria-controls="requests" aria-selected="false">
                            Yêu cầu tham gia
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="groupTabsContent">
                    <!-- Tab Bài đăng -->
                    <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                        <div class="posts-container">
                            @if($posts && count($posts) > 0)
                                <?php $vitri = 0; ?>
                                @foreach($posts as $post)
                                    <?php
                                    $vitri++;
                                    if ($vitri % 6 == 0 && isset($adsense_code)) {
                                        echo '<div class="post-card">' . $adsense_code . '</div>';
                                    }
                                    ?>
                                    <div class="post-card {{ $post->status == 0 ? 'postprivate' : '' }}" style="position: relative; padding-top:32px; margin-top:5px">
                                        <div class="action-buttons" style="position: absolute; top: -0px; right: 4px;z-index:1000 ;margin:3px">
                                            @if($post->slug)
                                                <a><button onclick="openPopup('{{ $post->slug }}')" class="deletebtn">
                                                    <i class="fa fa-eye" style="background:white"></i>
                                                </button></a>
                                            @endif

                                            @if(Auth::check() && (Auth::id() == $post->user_id || Auth::user()->role == 'admin'))
                                                @if($post->status == 1)
                                                    <a href="{{ route('front.tblogs.status', $post->id) }}" title="ẩn bài viết">
                                                        <button class="deletebtn">
                                                            <i class="fa fa-times-circle" style="background:white"></i>
                                                        </button>
                                                    </a>
                                                @else
                                                    <a href="{{ route('front.tblogs.status', $post->id) }}" title="công khai bài viết">
                                                        <button class="deletebtn">
                                                            <i class="fa fa-check-circle" style="background:white"></i>
                                                        </button>
                                                    </a>
                                                @endif
                                                
                                                <a href="{{ route('front.tblogs.edit', $post->id) }}">
                                                    <button class="deletebtn">
                                                        <i class="fa fa-pencil" style="background:white"></i>
                                                    </button>
                                                </a>

                                                <form action="{{ route('front.tblogs.destroy', $post->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="deletebtn" onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');">
                                                        <i class="fa fa-trash-o" style="background:white"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <div class="post-item">
                                            @if($post->photo && is_array($post->photo) && !empty($post->photo))
                                                <div class="post-image">
                                                    <a href="{{ route('front.tblogs.show', $post->slug) }}">
                                                        <img src="{{ $post->photo[0] }}" alt="{{ $post->title }}">
                                                    </a>
                                                </div>
                                            @endif

                                            <div class="post-author">
                                                @if($post->author)
                                                    <a href="{{ $post->user_url ?? '#' }}">
                                                        <img src="{{ $post->author->photo ?? asset('images/default-avatar.jpg') }}" 
                                                             alt="{{ $post->author->name ?? 'Author' }}" 
                                                             class="author-avatar">
                                                    </a>
                                                    <div class="author-info">
                                                        <h3>
                                                            <a href="{{ $post->user_url ?? '#' }}">
                                                                {{ $post->author->full_name ?? $post->author->name ?? 'Unknown Author' }}
                                                            </a>
                                                        </h3>
                                                        <p>{{ $post->created_at ? \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') : '' }}</p>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="post-content">
                                                <h2 class="post-title">
                                                    <a href="{{ route('front.tblogs.show', $post->slug) }}">{{ $post->title }}</a>
                                                </h2>
                                                @if($post->tags && count($post->tags) > 0)
                                                    <div class="post-tags">
                                                        @foreach($post->tags as $tag)
                                                            <a href="{{ route('front.tblogs.tag', $tag->slug) }}">
                                                                <span>#{{ $tag->title }}</span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                            {!! $post->actionbar !!}
                                        </div>
                                    </div>
                                    <div id="comment-box-{{ $post->id }}" class="comment-box" style="display: none; margin-bottom: 20px;">
                                        {!! $post->commenthtml !!}
                                    </div>
                                @endforeach

                                <div class="d-flex justify-content-center mt-4">
                                    {{ $posts->links() }}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Chưa có bài viết nào trong nhóm này.
                                    @if(Auth::check() && $isMember)
                                        <a href="{{ route('front.tblogs.create', ['group_id' => $group->id]) }}" class="btn btn-primary btn-sm ml-3">
                                            <i class="fas fa-plus"></i> Tạo bài viết mới
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tab Thành viên -->
                    <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Thành viên nhóm ({{ $members->count() }})</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ảnh đại diện</th>
                                                <th>Tên thành viên</th>
                                                <th>Email</th>
                                                <th>Vai trò</th>
                                                @if($isAdmin || $isModerator)
                                                    <th>Thao tác</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Hiển thị trưởng nhóm đầu tiên -->
                                            <tr>
                                                <td>
                                                    <img src="{{ $group->author && $group->author->photo ? asset($group->author->photo) : asset('images/default-avatar.jpg') }}" 
                                                         alt="{{ $group->author ? $group->author->full_name : 'Trưởng nhóm' }}" 
                                                         class="rounded-circle"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                </td>
                                                <td>{{ $group->author ? $group->author->full_name : 'N/A' }}</td>
                                                <td>{{ $group->author ? $group->author->email : 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-warning">Trưởng nhóm</span>
                                                </td>
                                                <td>-</td>
                                            </tr>

                                            <!-- Hiển thị các thành viên khác -->
                                            @foreach($members as $member)
                                                @if($member && $member->id != $group->author_id)
                                                    <tr>
                                                        <td>
                                                            <img src="{{ $member->photo ?? asset('images/default-avatar.jpg') }}" 
                                                                 alt="{{ $member->full_name ?? $member->name ?? 'Thành viên' }}" 
                                                                 class="rounded-circle"
                                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                                        </td>
                                                        <td>{{ $member->full_name ?? $member->name ?? 'N/A' }}</td>
                                                        <td>{{ $member->email ?? 'N/A' }}</td>
                                                        <td>
                                                            @if(in_array($member->id, json_decode($group->moderators ?? '[]', true)))
                                                                <span class="badge bg-info">Phó nhóm</span>
                                                            @else
                                                                <span class="badge bg-secondary">Thành viên</span>
                                                            @endif
                                                        </td>
                                                        @if($isAdmin || ($isModerator && !in_array($member->id, json_decode($group->moderators ?? '[]', true))))
                                                            <td>
                                                                @if($isAdmin)
                                                                    @if(!in_array($member->id, json_decode($group->moderators ?? '[]', true)))
                                                                        <form action="{{ route('group.promote-moderator', ['id' => $group->id, 'user_id' => $member->id]) }}" 
                                                                              method="POST" 
                                                                              class="d-inline-block">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Nâng làm phó nhóm">
                                                                                <i class="fas fa-user-shield"></i>
                                                                            </button>
                                                                        </form>
                                                                    @else
                                                                        <form action="{{ route('group.demote-moderator', ['id' => $group->id, 'user_id' => $member->id]) }}" 
                                                                              method="POST" 
                                                                              class="d-inline-block">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Hạ xuống thành viên">
                                                                                <i class="fas fa-user"></i>
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                @endif

                                                                <form action="{{ route('group.remove-member', ['id' => $group->id, 'user_id' => $member->id]) }}" 
                                                                      method="POST" 
                                                                      class="d-inline-block">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa khỏi nhóm"
                                                                            onclick="return confirm('Bạn có chắc muốn xóa thành viên này khỏi nhóm?')">
                                                                        <i class="fas fa-user-minus"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        @else
                                                            <td>-</td>
                                                        @endif
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Yêu cầu tham gia -->
                    <div class="tab-pane fade" id="requests" role="tabpanel" aria-labelledby="requests-tab">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Yêu cầu tham gia ({{ count($joinRequests) }})</h5>
                                @if(count($joinRequests) > 0)
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Người dùng</th>
                                                    <th>Email</th>
                                                    <th>Thời gian yêu cầu</th>
                                                    @if($isAdmin || $isModerator)
                                                    <th>Thao tác</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($joinRequests as $request)
                                                    @if($request && $request->user)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ $request->user->photo ?? asset('images/default-avatar.jpg') }}" 
                                                                         class="rounded-circle me-2" 
                                                                         width="40" 
                                                                         alt="{{ $request->user->full_name ?? $request->user->name ?? 'User' }}">
                                                                    <span>{{ $request->user->full_name ?? $request->user->name ?? 'N/A' }}</span>
                                                                </div>
                                                            </td>
                                                            <td>{{ $request->user->email ?? 'N/A' }}</td>
                                                            <td>{{ $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                                            @if($isAdmin || $isModerator)
                                                            <td>
                                                                <form action="{{ route('group.approve-member', ['id' => $group->id]) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="user_id" value="{{ $request->user->id }}">
                                                                    <button type="submit" class="btn btn-success btn-sm">
                                                                        <i class="fas fa-check"></i> Chấp nhận
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('group.reject-member', ['id' => $group->id]) }}" method="POST" class="d-inline ms-2">
                                                                    @csrf
                                                                    <input type="hidden" name="user_id" value="{{ $request->user->id }}">
                                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                                        <i class="fas fa-times"></i> Từ chối
                                                                    </button>
                                                                </form>
                                                            </td>
                                                            @endif
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-0">
                                        Không có yêu cầu tham gia nào.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <h4><i class="fas fa-lock"></i> Đây là nhóm riêng tư</h4>
                    <p>Bạn cần phải là thành viên để xem nội dung của nhóm.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Xử lý hiển thị tab
    document.addEventListener('DOMContentLoaded', function() {
        const triggerTabList = [].slice.call(document.querySelectorAll('#groupTabs button'));
        triggerTabList.forEach(function(triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });
    });

    // Xử lý hiển thị/ẩn comments
    function toggleComments(postId) {
        const commentBox = document.getElementById(`comments-${postId}`);
        if (commentBox) {
            commentBox.style.display = commentBox.style.display === 'none' ? 'block' : 'none';
        }
    }

    // Xử lý like bài viết
    document.addEventListener('DOMContentLoaded', function() {
        const likeButtons = document.querySelectorAll('.like-button');
        likeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.dataset.postId;
                fetch(`/group/post/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const countSpan = this.querySelector('span');
                        const icon = this.querySelector('i');
                        countSpan.textContent = `Thích (${data.likes_count})`;
                        
                        if (data.liked) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            icon.style.color = '#dc3545';
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            icon.style.color = '';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });

    function likePost(postId) {
        // Implement like functionality
        alert('Like functionality will be implemented');
    }

    function showComments(postId) {
        // Implement comments display
        alert('Comments functionality will be implemented');
    }

    function sharePost(postId) {
        // Implement share functionality
        alert('Share functionality will be implemented');
    }

    function deletePost(postId) {
        if(confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
            // Implement delete functionality
            alert('Delete functionality will be implemented');
        }
    }
</script>
@endpush
@endsection