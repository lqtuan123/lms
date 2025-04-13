@extends('Tuongtac::frontend.blogs.body')

@section('topcss')
<link rel="stylesheet" href="{{ asset('frontend/assets_f/custom-group.css') }}">
@endsection

@section('inner-content')
    <div class="top-bar">
        <!-- Nút tìm kiếm -->
        <form action="{{ route('group.index') }}" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Tìm kiếm nhóm..."
                value="{{ request('search') }}">
            <button type="submit" class="btn-search">Tìm kiếm</button>
        </form>

        <!-- Nút thêm nhóm -->
        @auth
            <a href="{{ route('group.create') }}" class="btn-add">Tạo nhóm mới</a>
        @else
            <a href="{{ route('front.login') }}" class="btn-add">Đăng nhập để tạo nhóm</a>
        @endauth
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="groupTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="public-tab" data-bs-toggle="tab" href="#public" role="tab"
                aria-controls="public" aria-selected="true">Nhóm Công Khai</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="private-tab" data-bs-toggle="tab" href="#private" role="tab" aria-controls="private"
                aria-selected="false">Nhóm Riêng Tư</a>
        </li>
        @auth
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="my-groups-tab" data-bs-toggle="tab" href="#my-groups" role="tab"
                    aria-controls="my-groups" aria-selected="false">Nhóm Của Tôi</a>
            </li>
        @endauth
    </ul>

    <div class="tab-content">
        <!-- Public Groups Tab -->
        <div class="tab-pane fade show active" id="public" role="tabpanel" aria-labelledby="public-tab">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="10%">Ảnh</th>
                        <th width="25%">Tên Nhóm</th>
                        <th width="15%">Loại</th>
                        <th width="15%">Thành viên</th>
                        <th width="10%">Loại</th>
                        <th width="20%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($publicGroups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td><img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                    alt="{{ $group->title }}" class="group-img"></td>
                            <td><a href="{{ route('group.show', $group->id) }}">{{ $group->title }}</a></td>
                            <td>{{ $group->type_code }}</td>
                            <td>
                                @php
                                    $members = json_decode($group->members ?? '[]', true);
                                    $memberCount = count($members);
                                @endphp
                                {{ $memberCount }} thành viên
                            </td>
                            <td>
                                <span class="badge badge-public">Công khai</span>
                            </td>
                            <td class="btn-groups">
                                <a href="{{ route('group.show', $group->id) }}" class="btn btn-info btn-sm">Xem</a>
                                @auth
                                    @if (auth()->user()->id === $group->author_id)
                                        <a href="{{ route('group.edit', $group->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                                    @else
                                        @php
                                            $pending = json_decode($group->pending_members ?? '[]', true);
                                            $members = json_decode($group->members ?? '[]', true);
                                        @endphp

                                        @if (!in_array(auth()->id(), $members))
                                            <form action="{{ route('group.join', $group->id) }}" method="POST">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Không có nhóm công khai nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if (is_object($publicGroups) && $publicGroups instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-center">
                    {{ $publicGroups->links() }}
                </div>
            @endif
        </div>

        <!-- Private Groups Tab -->
        <div class="tab-pane fade" id="private" role="tabpanel" aria-labelledby="private-tab">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="10%">Ảnh</th>
                        <th width="25%">Tên Nhóm</th>
                        <th width="15%">Loại</th>
                        <th width="15%">Thành viên</th>
                        <th width="10%">Loại</th>
                        <th width="20%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($privateGroups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td><img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                    alt="{{ $group->title }}" class="group-img"></td>
                            <td><a href="{{ route('group.show', $group->id) }}">{{ $group->title }}</a></td>
                            <td>{{ $group->type_code }}</td>
                            <td>
                                @php
                                    $members = json_decode($group->members ?? '[]', true);
                                    $memberCount = count($members);
                                @endphp
                                {{ $memberCount }} thành viên
                            </td>
                            <td>
                                <span class="badge badge-private">Riêng tư</span>
                            </td>
                            <td class="btn-groups">
                                <a href="{{ route('group.show', $group->id) }}" class="btn btn-info btn-sm">Xem</a>
                                @auth
                                    @if (auth()->user()->id === $group->author_id)
                                        <a href="{{ route('group.edit', $group->id) }}"
                                            class="btn btn-warning btn-sm">Sửa</a>
                                    @else
                                        @php
                                            $pending = json_decode($group->pending_members ?? '[]', true);
                                            $members = json_decode($group->members ?? '[]', true);
                                        @endphp

                                        @if (!in_array(auth()->id(), $members))
                                            <form action="{{ route('group.request-join', $group->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">Yêu cầu tham
                                                    gia</button>
                                            </form>
                                        @else
                                            <form action="{{ route('group.leave', $group->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">Rời nhóm</button>
                                            </form>
                                        @endif
                                    @endif
                                @endauth
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Không có nhóm riêng tư nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if (is_object($privateGroups) && $privateGroups instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-center">
                    {{ $privateGroups->links() }}
                </div>
            @endif
        </div>

        <!-- My Groups Tab -->
        @auth
            <div class="tab-pane fade" id="my-groups" role="tabpanel" aria-labelledby="my-groups-tab">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">ID</th>
                            <th width="10%">Ảnh</th>
                            <th width="25%">Tên Nhóm</th>
                            <th width="15%">Loại</th>
                            <th width="15%">Thành viên</th>
                            <th width="10%">Loại</th>
                            <th width="20%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userGroups as $group)
                            <tr>
                                <td>{{ $group->id }}</td>
                                <td><img src="{{ $group->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $group->title }}" class="group-img"></td>
                                <td><a href="{{ route('group.show', $group->id) }}">{{ $group->title }}</a></td>
                                <td>{{ $group->type_code }}</td>
                                <td>
                                    @php
                                        $members = json_decode($group->members ?? '[]', true);
                                        $memberCount = count($members);
                                    @endphp
                                    {{ $memberCount }} thành viên
                                </td>
                                <td>
                                    <span class="badge {{ $group->is_private ? 'badge-private' : 'badge-public' }}">
                                        {{ $group->is_private ? 'Riêng tư' : 'Công khai' }}
                                    </span>
                                </td>
                                <td class="btn-groups">
                                    <a href="{{ route('group.show', $group->id) }}" class="btn btn-info btn-sm">Xem</a>
                                    @if (auth()->user()->id === $group->author_id)
                                        <a href="{{ route('group.edit', $group->id) }}"
                                            class="btn btn-warning btn-sm">Sửa</a>
                                    @else
                                        <form action="{{ route('group.leave', $group->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">Rời nhóm</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Bạn chưa tham gia vào nhóm nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if (is_object($userGroups) && $userGroups instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-center">
                        {{ $userGroups->links() }}
                    </div>
                @endif
            </div>
        @endauth
    </div>
@endsection
