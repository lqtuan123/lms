@extends('frontend.layouts.master')

@section('content')
    <div class="container">
        <h3 class="intro-y text-lg font-medium mt-10">
            Sách đã đăng
        </h3>

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            {{-- <form method="GET" action="{{ route('user.books.index') }}" class="flex items-center gap-2">
                <select name="type_id" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">-- Tất cả loại sách --</option>
                    @foreach ($booktypes as $type)
                        <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->title }}
                        </option>
                    @endforeach
                </select>
            </form> --}}
            <div class="hidden md:block mx-auto text-slate-500">
                Hiển thị trang {{ $books->currentPage() }} trong {{ $books->lastPage() }} trang
            </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-slate-500">
                    {{-- <form action="{{ route('user.books.index') }}" method="get">
                        <input type="text" name="datasearch" class="form-control w-56 box pr-10"
                            placeholder="Tìm kiếm..." value="{{ request('datasearch') }}">
                        <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                    </form> --}}
                </div>
            </div>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible mt-5">
            <style>
                .title-cell,
                .summary-cell {
                    max-width: 100px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                .blocked-row {
                    background-color: #fff1f0;
                }
                .block-badge {
                    display: inline-block;
                    background-color: #ff3b30;
                    color: white;
                    font-size: 10px;
                    padding: 2px 6px;
                    border-radius: 10px;
                    margin-left: 5px;
                }
                .disabled-action {
                    opacity: 0.5;
                    cursor: not-allowed;
                    pointer-events: none;
                }
            </style>

            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">TÊN</th>
                        <th class="whitespace-nowrap">PHOTO</th>
                        <th class="whitespace-nowrap">NGƯỜI TẢI LÊN</th>
                        <th class="whitespace-nowrap">LOẠI SÁCH</th>
                        <th class="whitespace-nowrap">TÓM TẮT</th>
                        <th class="text-center whitespace-nowrap">TRẠNG THÁI</th>
                        <th class="text-center whitespace-nowrap">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr class="intro-x {{ $book->block === 'yes' ? 'blocked-row' : '' }}">
                            <td class="title-cell">
                                <a href="{{ route('front.book.show', $book->slug) }}"
                                    class="font-medium whitespace-nowrap">{{ $book->title }}</a>
                                @if($book->block === 'yes')
                                    <span class="block-badge">Đã bị chặn</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <img src="{{ $book->photo }}" alt="{{ $book->title }}"
                                    style="width: 50px; height: auto;">
                            </td>
                            <td>{{ $book->user->full_name ?? 'N/A' }}</td>
                            <td>{{ $book->bookType->title ?? 'N/A' }}</td>
                            <td class="summary-cell">{{ $book->summary ?? 'N/A' }}</td>
                            <td class="text-center">
                                <input type="checkbox" name="toggle" value="{{ $book->id }}"
                                    data-url="{{ route('user.books.status', $book->id) }}"
                                    {{ $book->status === 'active' ? 'checked' : '' }}
                                    {{ $book->block === 'yes' ? 'disabled' : '' }}>
                            </td>
                            <td class="table-report__action">
                                <div class="flex justify-center items-center">
                                    <a href="{{ route('user.books.edit', $book->id) }}" 
                                       class="flex items-center mr-3 {{ $book->block === 'yes' ? 'disabled-action' : '' }}"
                                       title="{{ $book->block === 'yes' ? 'Sách đã bị chặn, không thể chỉnh sửa' : 'Chỉnh sửa' }}">
                                        <i data-lucide="check-square" class="w-4 h-4 mr-1"></i>
                                    </a>
                                    <form action="{{ route('user.books.destroy', $book->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <a href="javascript:;" 
                                           class="flex items-center text-danger dltBtn {{ $book->block === 'yes' ? 'disabled-action' : '' }}"
                                           data-id="{{ $book->id }}" data-tw-toggle="modal"
                                           data-tw-target="#delete-confirmation-modal" 
                                           title="{{ $book->block === 'yes' ? 'Sách đã bị chặn, không thể xóa' : 'Xóa' }}">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>
                                        </a>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-500">Không có sách nào được đăng.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
            <nav class="w-full sm:w-auto sm:mr-auto">
                {{ $books->links('vendor.pagination.tailwind') }}
            </nav>
        </div>
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
            e.preventDefault();
            // Kiểm tra xem nút có bị vô hiệu hóa không
            if ($(this).hasClass('disabled-action')) {
                Swal.fire({
                    title: 'Không thể thực hiện',
                    text: 'Sách này đã bị chặn bởi quản trị viên',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }
            
            var bookId = $(this).data('id');
            var form = $(this).closest('form');
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
            // Kiểm tra xem checkbox có bị disabled không
            if ($(this).prop('disabled')) {
                return false;
            }
            
            var mode = $(this).prop('checked');
            var id = $(this).val();
            var url = $(this).data('url'); // 👈 Lấy URL từ data-url

            $.ajax({
                url: url,
                type: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                },
                success: function(response) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: response.msg,
                        showConfirmButton: false,
                        timer: 1000
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'Không thể thay đổi trạng thái của sách đã bị chặn',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                }
            });
        });
    </script>
@endsection
