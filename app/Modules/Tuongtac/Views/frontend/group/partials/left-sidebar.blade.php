<!-- Left Sidebar -->

<div class="group-sidebar-section">
    <!-- Sidebar Toggle Button (Mobile) -->
    <button id="sidebar-toggle"
        class="lg:hidden w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md mb-4 flex items-center justify-between">
        <span>Thông tin nhóm</span>
        <i class="fas fa-bars"></i>
    </button>

    <!-- Group Info -->
    <div class="group-sidebar-card mb-4">
        <div class="group-sidebar-card-body">
            <h3 class="group-sidebar-heading">
                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                Giới thiệu nhóm
            </h3>
            <p class="text-gray-700 text-sm">
                {{ $group->description }}
            </p>
            <div class="mt-3">
                @if (isset($group->groupType))
                    <div class="flex items-center text-gray-600 text-sm mb-1">
                        <i class="fas fa-tag mr-2"></i>
                        <span>Loại nhóm:
                            <span class="text-blue-500">{{ $group->groupType->title }}</span>
                        </span>
                    </div>
                @endif
                <div class="flex items-center text-gray-600 text-sm mb-1">
                    <i class="fas fa-user-shield mr-2"></i>
                    <span>Quản trị viên:
                        <a href="#" class="text-blue-500 hover:underline">
                            {{ $group->author ? $group->author->full_name : 'Nguyễn Văn A' }}
                        </a>
                    </span>
                </div>
                <div class="flex items-center text-gray-600 text-sm">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span>Ngày tạo: {{ date('d/m/Y', strtotime($group->created_at)) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Members -->
    <div class="group-sidebar-card mb-4">
        <div class="group-sidebar-card-body">
            <div class="flex justify-between items-center mb-3">
                <h3 class="group-sidebar-heading">
                    <i class="fas fa-users mr-2 text-blue-500"></i>
                    Thành viên
                    {{ $isMember || !$group->is_private ? '(' . count(json_decode($group->members ?? '[]', true)) . ')' : '' }}
                </h3>
                @if ($isMember || !$group->is_private)
                    <a href="javascript:void(0);" onclick="switchTab('members')" class="group-view-all-link">Xem tất
                        cả</a>
                @endif
            </div>

            <div class="group-members-grid">
                @if ($isMember || !$group->is_private)
                    @if (isset($members) && count($members) > 0)
                        @foreach ((is_array($members) ? collect($members) : $members)->take(6) as $member)
                            <a href="#" class="group-member-item">
                                <div class="group-member-avatar">
                                    <img src="{{ $member->photo ?? asset('images/default-avatar.jpg') }}"
                                        alt="{{ $member->full_name ?? 'Thành viên' }}">
                                </div>
                                <span
                                    class="group-member-name">{{ $member->full_name ?? ($member->name ?? 'Thành viên') }}</span>
                            </a>
                        @endforeach
                    @else
                        <p class="col-span-3 text-center text-gray-500 text-sm">Chưa có thành viên</p>
                    @endif
                @else
                    <p class="col-span-3 text-center text-gray-500 text-sm">
                        <i class="fas fa-lock mr-1"></i> Danh sách thành viên chỉ hiển thị cho thành viên nhóm
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Member Requests (for private groups) -->
    @if (Auth::check() &&
            (Auth::id() == $group->author_id || in_array(Auth::id(), json_decode($group->moderators ?? '[]', true))))
        <div id="member-requests" class="group-sidebar-card mb-4">
            <div class="group-sidebar-card-body">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="group-sidebar-heading">
                        <i class="fas fa-user-clock mr-2 text-blue-500"></i>
                        Yêu cầu tham gia ({{ isset($joinRequests) ? count($joinRequests) : 0 }})
                    </h3>
                    <a href="javascript:void(0);" onclick="switchTab('requests')" class="group-view-all-link">Xem tất
                        cả</a>
                </div>

                <div class="group-requests-list">
                    @if (isset($joinRequests) && (is_array($joinRequests) ? count($joinRequests) : $joinRequests->count()) > 0)
                        @foreach ((is_array($joinRequests) ? collect($joinRequests) : $joinRequests)->take(2) as $request)
                            @if ($request && isset($request->user))
                                <div class="group-request-item">
                                    <div class="flex items-center">
                                        <div class="group-request-avatar">
                                            <img src="{{ $request->user->photo ?? asset('images/default-avatar.jpg') }}"
                                                alt="{{ $request->user->full_name ?? 'User' }}">
                                        </div>
                                        <div class="group-request-info">
                                            <h4 class="group-request-name">
                                                {{ $request->user->full_name ?? ($request->user->name ?? 'N/A') }}
                                            </h4>
                                            <p class="group-request-time">
                                                {{ isset($request->created_at) && $request->created_at ? $request->created_at->diffForHumans() : 'Vừa xong' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-1">
                                        <form action="{{ route('group.approve-member', ['id' => $group->id]) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $request->user->id }}">
                                            <button type="submit" class="group-request-approve-btn">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('group.reject-member', ['id' => $group->id]) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $request->user->id }}">
                                            <button type="submit" class="group-request-reject-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-gray-500 text-sm">Không có yêu cầu mới</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Group Rules -->
    <div class="group-sidebar-card">
        <div class="group-sidebar-card-body">
            <h3 class="group-sidebar-heading">
                <i class="fas fa-gavel mr-2 text-blue-500"></i>
                Nội quy nhóm
            </h3>
            <ul class="group-rules-list">
                <li class="group-rule-item">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Không spam, quảng cáo không liên quan</span>
                </li>
                <li class="group-rule-item">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Tôn trọng các thành viên khác</span>
                </li>
                <li class="group-rule-item">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Chia sẻ kiến thức hữu ích</span>
                </li>
                <li class="group-rule-item">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Không chia sẻ nội dung vi phạm bản quyền</span>
                </li>
            </ul>
        </div>
    </div>
</div>


<style>
    .group-sidebar-section {
        display: flex;
        flex-direction: column;
    }

    .group-sidebar-card {
        background-color: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .group-sidebar-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transform: translateY(-2px);
    }

    .group-sidebar-card-body {
        padding: 1.25rem;
    }

    .group-sidebar-heading {
        font-weight: 600;
        font-size: 1.05rem;
        color: #374151;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
    }

    /* Member grid styles */
    .group-members-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }

    .group-member-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: all 0.3s ease;
    }

    .group-member-item:hover {
        transform: translateY(-3px);
    }

    .group-member-avatar {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 2px solid #ffffff;
        transition: all 0.3s ease;
        margin-bottom: 0.4rem;
    }

    .group-member-avatar:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.12);
        border-color: #dbeafe;
    }

    .group-member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: filter 0.3s ease;
    }

    .group-member-avatar:hover img {
        filter: brightness(1.05);
    }

    .group-member-name {
        font-size: 0.7rem;
        text-align: center;
        font-weight: 500;
        color: #4b5563;
        transition: color 0.3s ease;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .group-member-item:hover .group-member-name {
        color: #3b82f6;
    }

    /* Request styles */
    .group-requests-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .group-request-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        border-radius: 0.5rem;
        background-color: #f9fafb;
        transition: all 0.3s ease;
    }

    .group-request-item:hover {
        background-color: #f3f4f6;
        transform: translateX(3px);
    }

    .group-request-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        overflow: hidden;
        margin-right: 0.75rem;
        flex-shrink: 0;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .group-request-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .group-request-info {
        flex: 1;
    }

    .group-request-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        margin: 0;
    }

    .group-request-time {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .group-request-approve-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 0.25rem;
        background-color: #10b981;
        color: white;
        transition: all 0.3s ease;
        border: none;
        font-size: 0.75rem;
    }

    .group-request-approve-btn:hover {
        background-color: #059669;
        transform: scale(1.05);
    }

    .group-request-reject-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 0.25rem;
        background-color: #ef4444;
        color: white;
        transition: all 0.3s ease;
        border: none;
        font-size: 0.75rem;
    }

    .group-request-reject-btn:hover {
        background-color: #dc2626;
        transform: scale(1.05);
    }

    /* Rules styles */
    .group-rules-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .group-rule-item {
        display: flex;
        align-items: flex-start;
        font-size: 0.875rem;
        color: #4b5563;
        line-height: 1.5;
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: background-color 0.3s ease;
    }

    .group-rule-item:hover {
        background-color: #f9fafb;
    }

    .group-rule-item i {
        margin-top: 0.1rem;
        flex-shrink: 0;
    }

    /* View all link */
    .group-view-all-link {
        font-size: 0.8rem;
        color: #3b82f6;
        text-decoration: none;
        transition: all 0.3s ease;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .group-view-all-link:hover {
        color: #2563eb;
        background-color: #eff6ff;
        text-decoration: underline;
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .group-members-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .group-sidebar-card-body {
            padding: 1rem;
        }
    }
</style>
