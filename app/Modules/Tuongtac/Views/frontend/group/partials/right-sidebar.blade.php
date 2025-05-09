<!-- Right Sidebar -->

<div class="group-sidebar-section">
    <!-- Top Posts -->
    <div class="group-sidebar-card mb-4">
        <div class="group-sidebar-card-body">
            <h3 class="group-sidebar-heading">
                <i class="fas fa-fire mr-2 text-red-500"></i>
                Bài viết nổi bật
            </h3>

            <div class="group-top-posts">
                @forelse($topPosts as $post)
                    <div class="group-post-item">
                        <div class="group-post-thumbnail">
                            <img src="{{ $post->thumbnail }}" alt="{{ $post->title }}">
                        </div>
                        <div class="group-post-content">
                            <a href="{{ $post->url }}" class="group-post-title">{{ $post->title }}</a>
                            <div class="group-post-stats">
                                <span><i class="fas fa-thumbs-up text-blue-500"></i> {{ $post->likes_count }}</span>
                                <span><i class="fas fa-comment text-green-500"></i> {{ $post->comments_count }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="group-empty-notice">
                        <i class="fas fa-inbox text-gray-300"></i>
                        <p>Chưa có bài viết nổi bật</p>
                    </div>
                @endforelse
            </div>

            @if (count($topPosts) > 0)
                <a href="#" class="group-view-all-link">
                    <i class="fas fa-chevron-right"></i> Xem tất cả
                </a>
            @endif
        </div>
    </div>

    <!-- Top Members -->
    <div class="group-sidebar-card mb-4">
        <div class="group-sidebar-card-body">
            <h3 class="group-sidebar-heading">
                <i class="fas fa-user-friends mr-2 text-purple-500"></i>
                Thành viên tích cực
            </h3>

            <div class="group-top-members">
                @forelse($activeMembers as $member)
                    <div class="group-member-card">
                        <div class="group-member-avatar">
                            <img src="{{ $member->photo ?: asset('backend/assets/dist/images/profile-6.jpg') }}"
                                alt="{{ $member->name }}">
                        </div>
                        <div class="group-member-info">
                            <a href="{{ $member->url }}" class="group-member-name">{{ $member->name }}</a>
                            <p class="group-member-stats">
                                <span><i class="fas fa-newspaper"></i> {{ $member->blogs_count }}</span>
                                <span><i class="fas fa-reply"></i> {{ $member->interactions_count }}</span>
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="group-empty-notice">
                        <i class="fas fa-users text-gray-300"></i>
                        <p>Chưa có thành viên tích cực</p>
                    </div>
                @endforelse
            </div>

            @if (count($activeMembers) > 0)
                <a href="#" class="group-view-all-link">
                    <i class="fas fa-chevron-right"></i> Xem tất cả
                </a>
            @endif
        </div>
    </div>

    <!-- Similar Groups -->
    <div class="group-sidebar-card mb-4">
        <div class="group-sidebar-card-body">
            <h3 class="group-sidebar-heading">
                <i class="fas fa-users mr-2 text-green-500"></i>
                Nhóm tương tự
            </h3>

            <div class="group-similar-groups">
                @php
                    // Lấy ngẫu nhiên 5 nhóm khác với nhóm hiện tại
                    $similarGroups = \App\Modules\Group\Models\Group::where('id', '!=', $group->id)
                        ->inRandomOrder()
                        ->take(5)
                        ->get();
                @endphp

                @forelse($similarGroups as $similarGroup)
                    <div class="group-similar-item">
                        <div class="group-similar-avatar {{ !$similarGroup->photo ? 'group-avatar-default' : '' }}">
                            @if ($similarGroup->photo)
                                <img src="{{ asset($similarGroup->photo) }}" alt="{{ $similarGroup->title }}">
                            @else
                                <i class="fas fa-users"></i>
                            @endif
                        </div>
                        <div class="group-similar-content">
                            <a href="{{ route('group.show', $similarGroup->id) }}" class="group-similar-title">
                                {{ $similarGroup->title }}
                            </a>
                            <div class="group-similar-stats">
                                <span><i class="fas fa-user-friends"></i>
                                    {{ count(json_decode($similarGroup->members ?? '[]', true)) }}</span>
                                <span
                                    class="group-privacy {{ $similarGroup->is_private ? 'group-private' : 'group-public' }}">
                                    <i class="fas {{ $similarGroup->is_private ? 'fa-lock' : 'fa-globe-asia' }}"></i>
                                    {{ $similarGroup->is_private ? 'Riêng tư' : 'Công khai' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="group-empty-notice">
                        <i class="fas fa-search text-gray-300"></i>
                        <p>Không tìm thấy nhóm tương tự</p>
                    </div>
                @endforelse
            </div>

            <a href="{{ route('group.index') }}" class="group-view-all-link">
                <i class="fas fa-arrow-right"></i> Xem thêm nhóm khác
            </a>
        </div>
    </div>

    <!-- Group Tags -->
    <div class="group-sidebar-card">
        <div class="group-sidebar-card-body">
            <h3 class="group-sidebar-heading">
                <i class="fas fa-tags mr-2 text-blue-500"></i>
                Chủ đề nhóm
            </h3>

            <div class="group-tags-container">
                <a href="#" class="group-tag">Lập trình</a>
                <a href="#" class="group-tag">JavaScript</a>
                <a href="#" class="group-tag">React</a>
                <a href="#" class="group-tag">Node.js</a>
                <a href="#" class="group-tag">Clean Code</a>
                <a href="#" class="group-tag">Algorithm</a>
            </div>
        </div>
    </div>
</div>


<style>
    /* Main container */
    .group-sidebar-section {
        display: flex;
        flex-direction: column;
    }

    /* Card styling */
    .group-sidebar-card {
        background-color: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .group-sidebar-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transform: translateY(-2px);
    }

    .group-sidebar-card-body {
        padding: 1.25rem;
    }

    /* Headings */
    .group-sidebar-heading {
        font-weight: 600;
        font-size: 1.05rem;
        color: #374151;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    /* Top posts section */
    .group-top-posts {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .group-post-item {
        display: flex;
        align-items: flex-start;
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .group-post-item:hover {
        background-color: #f9fafb;
        transform: translateX(3px);
    }

    .group-post-thumbnail {
        width: 4rem;
        height: 4rem;
        border-radius: 0.375rem;
        overflow: hidden;
        background-color: #f3f4f6;
        flex-shrink: 0;
    }

    .group-post-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .group-post-content {
        margin-left: 0.75rem;
        flex: 1;
    }

    .group-post-title {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        margin-bottom: 0.25rem;
        line-height: 1.25;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        transition: color 0.3s ease;
    }

    .group-post-title:hover {
        color: #3b82f6;
    }

    .group-post-stats {
        display: flex;
        gap: 0.75rem;
        font-size: 0.75rem;
        color: #6b7280;
    }

    .group-post-stats span {
        display: flex;
        align-items: center;
    }

    .group-post-stats i {
        margin-right: 0.25rem;
    }

    /* Top members section */
    .group-top-members {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .group-member-card {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .group-member-card:hover {
        background-color: #f9fafb;
        transform: translateX(3px);
    }

    .group-member-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        overflow: hidden;
        background-color: #f3f4f6;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 2px solid #ffffff;
        transition: all 0.3s ease;
    }

    .group-member-card:hover .group-member-avatar {
        transform: scale(1.05);
        border-color: #dbeafe;
    }

    .group-member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .group-member-info {
        margin-left: 0.75rem;
        flex: 1;
    }

    .group-member-name {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        transition: color 0.2s ease;
    }

    .group-member-name:hover {
        color: #3b82f6;
    }

    .group-member-stats {
        display: flex;
        gap: 0.75rem;
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.125rem;
    }

    .group-member-stats span {
        display: flex;
        align-items: center;
    }

    .group-member-stats i {
        margin-right: 0.25rem;
        font-size: 0.7rem;
    }

    /* Similar groups section */
    .group-similar-groups {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .group-similar-item {
        display: flex;
        align-items: center;
        padding: 0.625rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .group-similar-item:hover {
        background-color: #f9fafb;
        transform: translateX(3px);
    }

    .group-similar-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        overflow: hidden;
        background-color: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .group-avatar-default {
        background-color: #dbeafe;
    }

    .group-avatar-default i {
        color: #3b82f6;
        font-size: 1rem;
    }

    .group-similar-item:hover .group-similar-avatar {
        transform: scale(1.05);
        border-color: #dbeafe;
    }

    .group-similar-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .group-similar-content {
        margin-left: 0.75rem;
        flex: 1;
    }

    .group-similar-title {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        transition: color 0.2s ease;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .group-similar-title:hover {
        color: #3b82f6;
    }

    .group-similar-stats {
        display: flex;
        gap: 0.75rem;
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.125rem;
    }

    .group-similar-stats span {
        display: flex;
        align-items: center;
    }

    .group-similar-stats i {
        margin-right: 0.25rem;
        font-size: 0.7rem;
    }

    .group-privacy {
        display: flex;
        align-items: center;
    }

    .group-private {
        color: #ef4444;
    }

    .group-public {
        color: #10b981;
    }

    /* Tag section */
    .group-tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .group-tag {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        background-color: #f3f4f6;
        color: #4b5563;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .group-tag:hover {
        background-color: #dbeafe;
        color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.15);
    }

    /* Empty notices */
    .group-empty-notice {
        padding: 1.5rem 1rem;
        text-align: center;
        color: #6b7280;
        font-size: 0.875rem;
        background-color: #f9fafb;
        border-radius: 0.5rem;
    }

    .group-empty-notice i {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    /* View all links */
    .group-view-all-link {
        display: block;
        text-align: center;
        color: #3b82f6;
        font-size: 0.875rem;
        margin-top: 1rem;
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
    }

    .group-view-all-link:hover {
        background-color: #eff6ff;
        color: #2563eb;
        text-decoration: underline;
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .group-sidebar-card-body {
            padding: 1rem;
        }

        .group-post-thumbnail {
            width: 3.5rem;
            height: 3.5rem;
        }
    }
</style>

<!-- Remove the mobile version as it's already being handled by media queries and body.blade.php -->
