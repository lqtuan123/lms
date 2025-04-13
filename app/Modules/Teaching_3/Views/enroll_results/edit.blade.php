@extends('backend.layouts.master')

@section('scriptop')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">
        Chỉnh sửa Kết Quả Học Tập
    </h2>
</div>
<div class="grid grid-cols-12 gap-12 mt-5">
    <div class="intro-y col-span-12 lg:col-span-12">
        <form method="post" action="{{ route('admin.enroll_results.update', $enrollResult->id) }}">
            @csrf
            @method('patch')
            <div class="intro-y box p-5">
                <!-- Enrollment -->
                <div class="mt-3">
                    <label for="enroll_id" class="form-label">Khóa học</label>
                    <select name="enroll_id" id="enroll_id" class="form-select" required>
                        @foreach($enrollments as $item)
                            <option value="{{ $item->id }}" {{ $item->id == $enrollResult->enroll_id ? 'selected' : '' }}>
                                {{ $item->phancong_id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sinh viên -->
                <div class="mt-3">
                    <label for="student_id" class="form-label">Sinh viên</label>
                    <select name="student_id" id="student_id" class="form-select" required>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $student->id == $enrollResult->student_id ? 'selected' : '' }}>
                                {{ $student->user->full_name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Điểm 30% -->
                <div class="mt-3">
                    <label for="diem30" class="form-label">Điểm 30%</label>
                    <input type="number" name="diem30" id="diem30" class="form-control" 
                           value="{{ old('diem30', $enrollResult->diem30) }}" step="0.1" min="0" max="30">
                </div>

                <!-- Điểm 70% -->
                <div class="mt-3">
                    <label for="diem70" class="form-label">Điểm 70%</label>
                    <input type="number" name="diem70" id="diem70" class="form-control" 
                           value="{{ old('diem70', $enrollResult->diem70) }}" step="0.1" min="0" max="70">
                </div>

                <!-- Hiển thị lỗi -->
                @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Nút submit -->
                <div class="text-right mt-5">
                    <button type="submit" class="btn btn-primary w-24">Cập nhật</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection


@section('scripts')
@endsection
