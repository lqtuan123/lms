@extends('backend.layouts.master')
@section('scriptop')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">
        Thêm kết quả khoá học
    </h2>
</div>
<div class="grid grid-cols-12 gap-12 mt-5">
    <div class="intro-y col-span-12 lg:col-span-12">
        <!-- BEGIN: Form Layout -->
        <form method="post" action="{{ route('admin.enroll_results.store') }}">
            @csrf
            <div class="intro-y box p-5">
                <!-- Enroll -->
                <div class="mt-3">
                    <label for="enroll_id" class="form-label">Chọn Enroll</label>
                    <select name="enroll_id" class="form-select mt-2" required>
                        @foreach($enrollments as $enroll)
                            <option value="{{ $enroll->id }}">{{ $enroll->phancong_id }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Student -->
                <div class="mt-3">
                    <label for="student_id" class="form-label">Chọn Sinh Viên</label>
                    <select name="student_id" class="form-select mt-2" required>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->user->full_name ?? 'Không có tên' }}</option>
                        @endforeach
                    </select>
                </div>

                
                <!-- Điểm 30% -->
                <div class="mt-3">
                    <label for="diem30" class="form-label">Điểm 30%</label>
                    <input type="number" id="diem30" name="diem30" class="form-control" step="0.01" min="0" max="30" value="{{ old('diem30') }}" placeholder="Nhập điểm (tùy chọn)">
                </div>
                
                <!-- Điểm 70% -->
                <div class="mt-3">
                    <label for="diem70" class="form-label">Điểm 70%</label>
                    <input type="number" id="diem70" name="diem70" class="form-control" step="0.01" min="0" max="70" value="{{ old('diem70') }}" placeholder="Nhập điểm (tùy chọn)">
                </div>
                
                <div class="text-right mt-5">
                    <button type="submit" class="btn btn-primary w-24">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection


@section('scripts')
<script>
    var select = new TomSelect('#select-tags', {
        maxItems: null,
        allowEmptyOption: true,
        plugins: ['remove_button'],
        sortField: {
            field: "text",
            direction: "asc"
        },
        onItemAdd: function() {
            this.setTextboxValue('');
            this.refreshOptions();
        },
        create: true
    });
    select.clear();
</script>

<script src="{{asset('js/js/ckeditor.js')}}"></script>
@endsection
