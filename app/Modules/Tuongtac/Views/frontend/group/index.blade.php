@extends('frontend.layouts.master')

@section('css')
    <style>
        .dropdown-menu {
            display: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        }

        .dropdown-menu.active {
            display: block;
            opacity: 1;
            visibility: visible;
        }

        .group-card {
            transition: all 0.2s ease;
        }

        .group-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .mobile-menu {
            display: none;
        }

        .mobile-menu.active {
            display: flex;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.active {
            display: block;
        }

        .tag:hover {
            transform: scale(1.05);
        }

        .tab-item {
            position: relative;
        }

        .tab-item.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #3b82f6;
        }

        .group-banner {
            height: 100px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .group-type-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
            font-size: 0.75rem;
        }

        .group-type-filter {
            display: flex;
            overflow-x: auto;
            padding: 0.5rem 0;
            margin-bottom: 1rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .group-type-filter::-webkit-scrollbar {
            height: 4px;
        }

        .group-type-filter::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 2px;
        }

        .group-type-filter .btn {
            white-space: nowrap;
            margin-right: 0.5rem;
        }

        .group-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .group-stat-item {
            flex: 1 1 auto;
            min-width: 120px;
            max-width: 200px;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
            border: 1px solid rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.2s ease;
        }

        .group-stat-item:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

        .search-filters {
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .group-stat-item {
                min-width: 100px;
            }

            .mobile-menu {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                z-index: 50;
                padding: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <div">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-2">Danh sách nhóm</h1>
                <p class="text-muted">Khám phá và tham gia các nhóm học tập để chia sẻ kiến thức và kết nối</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('group.create') }}" class="btn btn-primary" id="create-group-btn">
                    <i class="fas fa-plus mr-2"></i>Tạo nhóm mới
                </a>
            </div>
        </div>

        <!-- Bộ lọc tìm kiếm nhóm -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Tìm kiếm & Lọc nhóm</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('group.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Tên nhóm hoặc mô tả..." 
                                       name="keyword" value="{{ $keyword ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="type_code" class="form-select">
                                <option value="">-- Tất cả loại nhóm --</option>
                                @foreach($groupTypes as $type)
                                    <option value="{{ $type->type_code }}" {{ isset($selectedType) && $selectedType == $type->type_code ? 'selected' : '' }}>
                                        {{ $type->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filter pills cho mobile -->
        <div class="group-type-filter d-md-none mb-3">
            <a href="{{ route('group.index') }}" 
               class="btn {{ !isset($selectedType) ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                Tất cả
            </a>
            @foreach($groupTypes as $type)
                <a href="{{ route('group.index', ['type_code' => $type->type_code, 'keyword' => $keyword ?? '']) }}" 
                   class="btn {{ isset($selectedType) && $selectedType == $type->type_code ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    {{ $type->title }}
                </a>
            @endforeach
        </div>

        <!-- Tabs Danh sách nhóm -->
        <ul class="nav nav-tabs mb-4" id="groupsTabs" role="tablist">
            @if (Auth::check() && count($userGroups) > 0)
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="my-groups-tab" data-bs-toggle="tab" href="#my-groups" role="tab"
                        aria-controls="my-groups" aria-selected="true">
                        Nhóm của tôi ({{ count($userGroups) }})
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="public-tab" data-bs-toggle="tab" href="#public-groups" role="tab"
                        aria-controls="public-groups" aria-selected="false">
                        Nhóm công khai
                    </a>
                </li>
            @else
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="public-tab" data-bs-toggle="tab" href="#public-groups" role="tab"
                        aria-controls="public-groups" aria-selected="true">
                        Nhóm công khai
                    </a>
                </li>
            @endif
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="private-tab" data-bs-toggle="tab" href="#private-groups" role="tab"
                    aria-controls="private-groups" aria-selected="false">
                    Nhóm riêng tư
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Nhóm của tôi -->
            @if (Auth::check() && count($userGroups) > 0)
                <div class="tab-pane fade show active" id="my-groups" role="tabpanel" aria-labelledby="my-groups-tab">
                    <div class="row">
                        @foreach ($userGroups as $group)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card group-card h-100">
                                    <div class="position-relative">
                                        @if(isset($group->groupType))
                                            <div class="group-type-badge">
                                                <span class="badge bg-info">{{ $group->groupType->title }}</span>
                                            </div>
                                        @endif
                                        <div class="group-banner" style="background-image: url('{{ $group->cover_photo ?? asset('backend/assets/dist/images/preview-1.jpg') }}');">
                                        </div>
                                        <img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                            alt="{{ $group->title }}" class="rounded-circle position-absolute"
                                            style="width: 70px; height: 70px; bottom: -25px; left: 20px; border: 4px solid white; object-fit: cover;">
                                    </div>
                                    <div class="card-body pt-5 mt-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title mb-1">
                                                <a href="{{ route('group.show', $group->id) }}"
                                                    class="text-decoration-none text-dark">
                                                    {{ $group->title }}
                                                </a>
                                            </h5>
                                            <span class="badge {{ $group->is_private ? 'bg-danger' : 'bg-success' }}">
                                                {{ $group->is_private ? 'Riêng tư' : 'Công khai' }}
                                            </span>
                                        </div>

                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-users me-1"></i>
                                            @php
                                                $members = json_decode($group->members ?? '[]', true);
                                                $memberCount = count($members);
                                            @endphp
                                            {{ $memberCount }} thành viên
                                            @if(isset($group->groupType))
                                                <span class="mx-1">•</span>
                                                <i class="fas fa-tag me-1"></i>
                                                {{ $group->groupType->title }}
                                            @endif
                                        </p>

                                        <p class="card-text text-truncate mb-3" style="max-height: 60px; overflow: hidden;">
                                            {{ $group->description }}
                                        </p>

                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('group.show', $group->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-arrow-right me-1"></i> Xem
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade" id="public-groups" role="tabpanel" aria-labelledby="public-tab">
                @else
                    <div class="tab-pane fade show active" id="public-groups" role="tabpanel" aria-labelledby="public-tab">
            @endif
            <div class="row">
                @if($publicGroups->count() > 0)
                    @foreach ($publicGroups as $group)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card group-card h-100">
                                <div class="position-relative">
                                    @if(isset($group->groupType))
                                        <div class="group-type-badge">
                                            <span class="badge bg-info">{{ $group->groupType->title }}</span>
                                        </div>
                                    @endif
                                    <div class="group-banner" style="background-image: url('{{ $group->cover_photo ?? asset('backend/assets/dist/images/preview-1.jpg') }}');">
                                    </div>
                                    <img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $group->title }}" class="rounded-circle position-absolute"
                                        style="width: 70px; height: 70px; bottom: -25px; left: 20px; border: 4px solid white; object-fit: cover;">
                                </div>
                                <div class="card-body pt-5 mt-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-1">
                                            <a href="{{ route('group.show', $group->id) }}"
                                                class="text-decoration-none text-dark">
                                                {{ $group->title }}
                                            </a>
                                        </h5>
                                        <span class="badge bg-success">Công khai</span>
                                    </div>

                                    <p class="text-muted small mb-3">
                                        <i class="fas fa-users me-1"></i>
                                        @php
                                            $members = json_decode($group->members ?? '[]', true);
                                            $memberCount = count($members);
                                        @endphp
                                        {{ $memberCount }} thành viên
                                        @if(isset($group->groupType))
                                            <span class="mx-1">•</span>
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $group->groupType->title }}
                                        @endif
                                    </p>

                                    <p class="card-text text-truncate mb-3" style="max-height: 60px; overflow: hidden;">
                                        {{ $group->description }}
                                    </p>

                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('group.show', $group->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-arrow-right me-1"></i> Xem
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            @if(isset($selectedType) || isset($keyword))
                                Không tìm thấy nhóm nào phù hợp với bộ lọc đã chọn.
                                <a href="{{ route('group.index') }}" class="alert-link">Xóa bộ lọc</a>
                            @else
                                Hiện chưa có nhóm công khai nào.
                            @endif
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-center mt-4">
                    {{ $publicGroups->appends(['type_code' => $selectedType ?? null, 'keyword' => $keyword ?? null])->links() }}
                </div>
            </div>
        </div>

        <!-- Nhóm riêng tư -->
        <div class="tab-pane fade" id="private-groups" role="tabpanel" aria-labelledby="private-tab">
            <div class="row">
                @if($privateGroups->count() > 0)
                    @foreach ($privateGroups as $group)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card group-card h-100">
                                <div class="position-relative">
                                    @if(isset($group->groupType))
                                        <div class="group-type-badge">
                                            <span class="badge bg-info">{{ $group->groupType->title }}</span>
                                        </div>
                                    @endif
                                    <div class="group-banner" style="background-image: url('{{ $group->cover_photo ?? asset('backend/assets/dist/images/preview-1.jpg') }}');">
                                    </div>
                                    <img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $group->title }}" class="rounded-circle position-absolute"
                                        style="width: 70px; height: 70px; bottom: -25px; left: 20px; border: 4px solid white; object-fit: cover;">
                                    <div class="position-absolute" style="top: 10px; right: 10px;">
                                        <i class="fas fa-lock text-white"></i>
                                    </div>
                                </div>
                                <div class="card-body pt-5 mt-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-1">
                                            <a href="{{ route('group.show', $group->id) }}"
                                                class="text-decoration-none text-dark">
                                                {{ $group->title }}
                                            </a>
                                        </h5>
                                        <span class="badge bg-danger">Riêng tư</span>
                                    </div>

                                    <p class="text-muted small mb-3">
                                        <i class="fas fa-users me-1"></i>
                                        @php
                                            $members = json_decode($group->members ?? '[]', true);
                                            $memberCount = count($members);

                                            // Kiểm tra xem người dùng hiện tại có trong danh sách thành viên không
                                            $currentUserId = Auth::id();
                                            $isMember = $currentUserId && in_array($currentUserId, $members);
                                        @endphp
                                        @if ($isMember || !$group->is_private)
                                            {{ $memberCount }} thành viên
                                        @else
                                            <i class="fas fa-lock"></i> Nhóm riêng tư
                                        @endif
                                        @if(isset($group->groupType))
                                            <span class="mx-1">•</span>
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $group->groupType->title }}
                                        @endif
                                    </p>

                                    <p class="card-text text-truncate mb-3" style="max-height: 60px; overflow: hidden;">
                                        {{ $group->description }}
                                    </p>

                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('group.show', $group->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-arrow-right me-1"></i> Xem
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            @if(isset($selectedType) || isset($keyword))
                                Không tìm thấy nhóm riêng tư nào phù hợp với bộ lọc đã chọn.
                                <a href="{{ route('group.index') }}" class="alert-link">Xóa bộ lọc</a>
                            @else
                                Hiện chưa có nhóm riêng tư nào.
                            @endif
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-center mt-4">
                    {{ $privateGroups->appends(['type_code' => $selectedType ?? null, 'keyword' => $keyword ?? null])->links() }}
                </div>
            </div>
        </div>
    </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Giữ trạng thái tab khi tải lại trang (nếu có tham số truy vấn)
            @if(request()->has('type_code') || request()->has('keyword'))
                // Khi có tham số tìm kiếm, đảm bảo tab đang active được giữ lại
                const urlParams = new URLSearchParams(window.location.search);
                const tabParam = urlParams.get('tab');
                
                if (tabParam) {
                    $(`#${tabParam}-tab`).tab('show');
                }
            @endif
            
            // Cập nhật URL khi chuyển tab
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const tabId = $(e.target).attr('id').replace('-tab', '');
                const urlParams = new URLSearchParams(window.location.search);
                
                urlParams.set('tab', tabId);
                const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                
                // Thay đổi URL nhưng không reload trang
                window.history.pushState({}, '', newUrl);
            });
        });
    </script>
@endsection
