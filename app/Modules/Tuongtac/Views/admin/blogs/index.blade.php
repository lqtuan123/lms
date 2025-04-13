@extends('backend.layouts.master')

@section('content')
    <h2 class="intro-y text-lg font-medium mt-10">
        Quản lý bài viết
    </h2>

    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
        <a href="{{ route('admin.tblogs.create') }}" class="btn btn-primary shadow-md mr-2">Thêm bài viết</a>
        <div class="hidden md:block mx-auto text-slate-500">Hiển thị trang {{ $blogs->currentPage() }} trong
            {{ $blogs->lastPage() }} trang</div>
        <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
            <div class="w-56 relative text-slate-500">
                <form action="{{ route('admin.tblogs.index') }}" method="get">
                    <input type="text" name="title" class="form-control w-56 box pr-10" 
                        placeholder="Tìm kiếm..." value="{{ request('title') }}">
                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                </form>
            </div>
        </div>
    </div>

    <div class="intro-y col-span-12 overflow-auto lg:overflow-visible mt-5">
        <style>
            .title-cell {
                max-width: 200px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .content-cell {
                max-width: 300px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        </style>

        <table class="table table-report -mt-2">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">TIÊU ĐỀ</th>
                    <th class="whitespace-nowrap">HÌNH ẢNH</th>
                    <th class="whitespace-nowrap">TÁC GIẢ</th>
                    <th class="whitespace-nowrap">NỘI DUNG</th>
                    <th class="whitespace-nowrap">TAGS</th>
                    <th class="text-center whitespace-nowrap">TRẠNG THÁI</th>
                    <th class="text-center whitespace-nowrap">HÀNH ĐỘNG</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($blogs as $blog)
                    <tr class="intro-x">
                        <td class="title-cell">
                            <a href="{{ route('admin.tblogs.show', $blog->id) }}"
                                class="font-medium whitespace-nowrap">{{ $blog->title }}</a>
                        </td>

                        <td class="text-center">
                            @if($blog->photo)
                                @php
                                    $photos = is_array($blog->photo) ? $blog->photo : json_decode($blog->photo, true);
                                @endphp
                                @if(is_array($photos) && count($photos) > 0)
                                    <img src="{{ $photos[0] }}" alt="{{ $blog->title }}" style="width: 50px; height: auto;">
                                @elseif(is_string($blog->photo) && $blog->photo != '')
                                    <img src="{{ $blog->photo }}" alt="{{ $blog->title }}" style="width: 50px; height: auto;">
                                @else
                                    <span>Không có ảnh</span>
                                @endif
                            @else
                                <span>Không có ảnh</span>
                            @endif
                        </td>

                        <td>{{ $blog->author ? $blog->author->full_name : 'N/A' }}</td>

                        <td class="content-cell">{!! Str::limit(strip_tags($blog->content), 100) !!}</td>

                        <td>
                            @if(isset($blog->tags) && count($blog->tags) > 0)
                                @foreach($blog->tags as $tag)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                                        {{ $tag->title }}
                                    </span>
                                @endforeach
                            @else
                                <span>Không có tags</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <input type="checkbox" data-toggle="switchbutton" data-onlabel="active" data-offlabel="inactive"
                                {{ $blog->status == 1 ? 'checked' : '' }} data-size="sm" name="toggle"
                                value="{{ $blog->id }}" data-style="ios">
                        </td>

                        <td class="table-report__action">
                            <div class="flex justify-center items-center">
                                <a href="{{ route('admin.tblogs.edit', $blog->id) }}" class="flex items-center mr-3"
                                    title="Chỉnh sửa">
                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i>
                                </a>
                                <form action="{{ route('admin.tblogs.destroy', $blog->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <a class="flex items-center text-danger dltBtn" data-id="{{ $blog->id }}"
                                        href="javascript:;" data-tw-toggle="modal"
                                        data-tw-target="#delete-confirmation-modal" title="Xóa">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>
                                    </a>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Không có bài viết nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
        <nav class="w-full sm:w-auto sm:mr-auto">
            {{ $blogs->appends(request()->all())->links('vendor.pagination.tailwind') }}
        </nav>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script>
        $('.dltBtn').click(function(e) {
            var blogId = $(this).data('id');
            var form = $(this).closest('form');
            e.preventDefault();
            Swal.fire({
                title: 'Bạn có chắc muốn xóa không?',
                text: "Bạn không thể lấy lại dữ liệu sau khi xóa",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Vâng, tôi muốn xóa!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        $("[name='toggle']").change(function() {
            var mode = $(this).prop('checked');
            var id = $(this).val();
            $.ajax({
                url: "{{ route('admin.tblogs.status') }}",
                type: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function(response) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: response.msg,
                        showConfirmButton: false,
                        timer: 1000
                    });
                }
            });
        });
    </script>
@endsection 