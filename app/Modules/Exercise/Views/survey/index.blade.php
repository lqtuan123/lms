@extends('backend.layouts.master')

@section('content')
<h2 class="intro-y text-lg font-medium mt-10">
    Danh sách Khảo Sát
</h2>
<div class="grid grid-cols-12 gap-6 mt-5">
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
        <a href="{{ route('admin.survey.create') }}" class="btn btn-primary shadow-md mr-2">Thêm Khảo Sát</a>

        <div class="hidden md:block mx-auto text-slate-500">Hiển thị trang {{ $surveys->currentPage() }} trong {{ $surveys->lastPage() }} trang</div>

        <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
            <div class="w-56 relative text-slate-500">
                <!-- Search form (commented out, as in sample) -->
                {{-- <form action="{{ route('admin.survey.search') }}" method="get">
                    @csrf
                    <input type="text" name="datasearch" class="ipsearch form-control w-56 box pr-10" placeholder="Search...">
                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                </form> --}}
            </div>
        </div>
    </div>

    <!-- BEGIN: Data List -->
    <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
        <table class="table table-report -mt-2">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">Tên Khảo Sát</th>
                    <th class="whitespace-nowrap">Học Phần</th>
                    <th class="whitespace-nowrap">Giảng Viên</th>
                    <th class="whitespace-nowrap">Số Câu Hỏi</th>
                    <th class="text-center whitespace-nowrap">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($surveys as $survey)
                <tr class="intro-x">
                    <td>
                        <a href="{{ route('admin.survey.show', $survey->id) }}" class="font-medium whitespace-nowrap">{{ $survey->title }}</a>
                    </td>
                    <td class="text-left">{{ $hocPhanList[$survey->hocphan_id] ?? 'N/A' }}</td>
                    <td class="text-left">{{ $survey->giangvien_id?? 'N/A' }}</td>
                    <td class="text-left">{{ $survey->question_count ?? 0 }}</td>
                    <td class="table-report__action w-56">
                        <div class="flex justify-center items-center">
                            <a href="{{ route('admin.survey.edit', $survey->id) }}" class="flex items-center mr-3">
                                <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> Sửa
                            </a>
                            <form action="{{ route('admin.survey.destroy', $survey->id) }}" method="post">
                                @csrf
                                @method('delete')
                                <a class="flex items-center text-danger dltBtn" data-id="{{ $survey->id }}" href="javascript:;" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Xóa
                                </a>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- END: Data List -->

    <!-- BEGIN: Pagination -->
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
        <nav class="w-full sm:w-auto sm:mr-auto">
            {{ $surveys->links('vendor.pagination.tailwind') }}
        </nav>
    </div>
    <!-- END: Pagination -->
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('.dltBtn').click(function(e) {
        var form = $(this).closest('form');
        var dataID = $(this).data('id');
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
</script>
@endsection