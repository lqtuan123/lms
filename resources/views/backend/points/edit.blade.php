@extends('backend.layouts.master')

@section('content')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Chỉnh sửa quy tắc điểm</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.points.index') }}" class="btn btn-warning shadow-md mr-2">
            <i data-lucide="corner-up-left" class="w-4 h-4 mr-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="grid grid-cols-12 gap-6 mt-5">
    <div class="intro-y col-span-12">
        <div class="intro-y box">
            <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">Thông tin quy tắc</h2>
            </div>
            <div id="basic-form" class="p-5">
                <form method="POST" action="{{ route('admin.points.update', $pointRule->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-12 xl:col-span-6">
                            <div class="mt-3">
                                <label for="name" class="form-label">Tên quy tắc <span class="text-danger">*</span></label>
                                <input id="name" type="text" name="name" class="form-control @error('name') border-danger @enderror" placeholder="Nhập tên quy tắc" value="{{ old('name', $pointRule->name) }}" required>
                                @error('name')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mt-3">
                                <label for="code" class="form-label">Mã quy tắc</label>
                                <input id="code" type="text" class="form-control bg-slate-100" value="{{ $pointRule->code }}" readonly disabled>
                                <div class="text-xs text-slate-500 mt-1">Mã quy tắc không thể thay đổi sau khi tạo</div>
                            </div>
                            <div class="mt-3">
                                <label for="point_value" class="form-label">Giá trị điểm <span class="text-danger">*</span></label>
                                <input id="point_value" type="number" name="point_value" class="form-control @error('point_value') border-danger @enderror" placeholder="Nhập số điểm" value="{{ old('point_value', $pointRule->point_value) }}" required>
                                @error('point_value')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-span-12 xl:col-span-6">
                            <div class="mt-3">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea id="description" name="description" class="form-control @error('description') border-danger @enderror" placeholder="Nhập mô tả" rows="4">{{ old('description', $pointRule->description) }}</textarea>
                                @error('description')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mt-3">
                                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-select @error('status') border-danger @enderror">
                                    <option value="active" {{ old('status', $pointRule->status) == 'active' ? 'selected' : '' }}>Kích hoạt</option>
                                    <option value="inactive" {{ old('status', $pointRule->status) == 'inactive' ? 'selected' : '' }}>Vô hiệu</option>
                                </select>
                                @error('status')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="text-right mt-5">
                        <button type="reset" class="btn btn-outline-secondary w-24 mr-1">Hủy</button>
                        <button type="submit" class="btn btn-primary w-24">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 