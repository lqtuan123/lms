@extends('Tuongtac::frontend.pages.body')

@section('topcss')
<style>
    .container {
        margin-top: 20px;
    }
    .group-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .group-card {
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    .group-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .group-card-header {
        position: relative;
        height: 120px;
        overflow: hidden;
    }
    .group-card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .group-card-body {
        padding: 15px;
    }
    .group-title {
        font-size: 1.2rem;
        margin-bottom: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .group-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #666;
        font-size: 0.9rem;
    }
    .group-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .badge-private {
        background-color: #dc3545;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 0.75rem;
    }
    .badge-public {
        background-color: #28a745;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 0.75rem;
    }
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 10px 15px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    .search-form {
        display: flex;
        flex: 1;
        max-width: 500px;
    }
    .search-input {
        flex: 1;
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 4px 0 0 4px;
        outline: none;
    }
    .btn-search {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 0 4px 4px 0;
        cursor: pointer;
    }
    .btn-add {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        margin-left: 10px;
    }
    .nav-tabs {
        margin-bottom: 20px;
    }
    .group-description {
        margin-bottom: 10px;
        color: #666;
        font-size: 0.9rem;
        height: 40px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .group-members {
        font-size: 0.85rem;
        color: #666;
        margin-top: 5px;
    }
</style>
@endsection

@section('inner-content')
    <div class="top-bar">
        <!-- Nút tìm kiếm -->
        <form action="{{ route('front.tpage.viewgroup', $page->slug) }}" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Tìm kiếm nhóm..." value="{{ request('search') }}">
            <button type="submit" class="btn-search">Tìm kiếm</button>
        </form>

        <!-- Nút thêm nhóm -->
        @auth
            <a href="{{ route('group.create') }}" class="btn-add">Tạo nhóm mới</a>
        @else
            <a href="{{ route('front.login') }}" class="btn-add">Đăng nhập để tạo nhóm</a>
        @endauth
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="groupTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="public-tab" data-bs-toggle="tab" href="#public" role="tab" aria-controls="public" aria-selected="true">Nhóm Công Khai</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="private-tab" data-bs-toggle="tab" href="#private" role="tab" aria-controls="private" aria-selected="false">Nhóm Riêng Tư</a>
        </li>
        @auth
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="my-groups-tab" data-bs-toggle="tab" href="#my-groups" role="tab" aria-controls="my-groups" aria-selected="false">Nhóm Của Tôi</a>
        </li>
        @endauth
    </ul>

    <div class="tab-content">
        <!-- Public Groups Tab -->
        <div class="tab-pane fade show active" id="public" role="tabpanel" aria-labelledby="public-tab">
            <div class="group-list">
                @forelse($publicGroups as $group)
                    @if($group->is_private == 0)
                    <div class="group-card">
                        <div class="group-card-header">
                            <img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}" alt="{{ $group->title }}" class="group-card-img">
                        </div>
                        <div class="group-card-body">
                            <h3 class="group-title">
                                <a href="{{ route('group.show', $group->id) }}">{{ $group->title }}</a>
                            </h3>
                            <div class="group-info">
                                <span class="badge badge-public">Công khai</span>
                            </div>
                            <div class="group-description">
                                {{ \Illuminate\Support\Str::limit($group->description, 100) }}
                            </div>
                            <div class="group-members">
                                @php
                                    $members = json_decode($group->members ?? '[]', true);
                                    $memberCount = count($members);
                                @endphp
                                <i class="fa fa-users"></i> {{ $memberCount }} thành viên
                            </div>
                            <div class="group-actions mt-3">
                                <a href="{{ route('group.show', $group->id) }}" class="btn btn-info btn-sm">Xem</a>
                                @auth
                                    @if(auth()->user()->id === $group->author_id)
                                        <a href="{{ route('group.edit', $group->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                                    @else
                                        @php
                                            $pending = json_decode($group->pending_members ?? '[]', true);
                                            $members = json_decode($group->members ?? '[]', true);
                                        @endphp
                                        
                                        @if(!in_array(auth()->id(), $members))
                                            <form action="{{ route('group.requestJoin', $group->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">Tham gia</button>
                                            </form>
                                        @else
                                            <form action="{{ route('group.leave', $group->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">Rời nhóm</button>
                                            </form>
                                        @endif
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="alert alert-info w-100">
                        Không có nhóm công khai nào
                    </div>
                @endforelse
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $publicGroups->links() }}
            </div>
        </div>
        
        <!-- Private Groups Tab -->
        <div class="tab-pane fade" id="private" role="tabpanel" aria-labelledby="private-tab">
            <div class="group-list">
                @forelse($publicGroups as $group)
                    @if($group->is_private == 1)
                    <div class="group-card">
                        <div class="group-card-header">
                            <img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}" alt="{{ $group->title }}" class="group-card-img">
                        </div>
                        <div class="group-card-body">
                            <h3 class="group-title">
                                <a href="{{ route('group.show', $group->id) }}">{{ $group->title }}</a>
                            </h3>
                            <div class="group-info">
                                <span class="badge badge-private">Riêng tư</span>
                            </div>
                            <div class="group-description">
                                {{ \Illuminate\Support\Str::limit($group->description, 100) }}
                            </div>
                            <div class="group-members">
                                @php
                                    $members = json_decode($group->members ?? '[]', true);
                                    $memberCount = count($members);
                                @endphp
                                <i class="fa fa-users"></i> {{ $memberCount }} thành viên
                            </div>
                            <div class="group-actions mt-3">
                                <a href="{{ route('group.show', $group->id) }}" class="btn btn-info btn-sm">Xem</a>
                                @auth
                                    @if(auth()->user()->id === $group->author_id)
                                        <a href="{{ route('group.edit', $group->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                                    @else
                                        @php
                                            $pending = json_decode($group->pending_members ?? '[]', true);
                                            $members = json_decode($group->members ?? '[]', true);
                                        @endphp
                                        
                                        @if(!in_array(auth()->id(), $members))
                                            <form action="{{ route('group.requestJoin', $group->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">Tham gia</button>
                                            </form>
                                        @else
                                            <form action="{{ route('group.leave', $group->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">Rời nhóm</button>
                                            </form>
                                        @endif
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="alert alert-info w-100">
                        Không có nhóm riêng tư nào
                    </div>
                @endforelse
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $publicGroups->links() }}
            </div>
        </div>
        
        <!-- My Groups Tab -->
        @auth
        <div class="tab-pane fade" id="my-groups" role="tabpanel" aria-labelledby="my-groups-tab">
            <div class="group-list">
                @forelse($userGroups as $group)
                    <div class="group-card">
                        <div class="group-card-header">
                            <img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}" alt="{{ $group->title }}" class="group-card-img">
                        </div>
                        <div class="group-card-body">
                            <h3 class="group-title">
                                <a href="{{ route('group.show', $group->id) }}">{{ $group->title }}</a>
                            </h3>
                            <div class="group-info">
                                <span class="badge {{ $group->is_private == 1 ? 'badge-private' : 'badge-public' }}">
                                    {{ $group->is_private == 1 ? 'Riêng tư' : 'Công khai' }}
                                </span>
                            </div>
                            <div class="group-description">
                                {{ \Illuminate\Support\Str::limit($group->description, 100) }}
                            </div>
                            <div class="group-members">
                                @php
                                    $members = json_decode($group->members ?? '[]', true);
                                    $memberCount = count($members);
                                @endphp
                                <i class="fa fa-users"></i> {{ $memberCount }} thành viên
                            </div>
                            <div class="group-actions mt-3">
                                <a href="{{ route('group.show', $group->id) }}" class="btn btn-info btn-sm">Xem</a>
                                @if(auth()->user()->id === $group->author_id)
                                    <a href="{{ route('group.edit', $group->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                                @else
                                    <form action="{{ route('group.leave', $group->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">Rời nhóm</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info w-100">
                        Bạn chưa tham gia vào nhóm nào
                    </div>
                @endforelse
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $userGroups->links() }}
            </div>
        </div>
        @endauth
    </div>
@endsection 