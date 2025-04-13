@extends('Tuongtac::frontend.blogs.body')

@section('topcss')
<style>
    .container {
        margin-top: 20px;
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    .form-group label {
        font-weight: bold;
    }
    .btn {
        width: 100%;
    }
    .required::after {
        content: " *";
        color: red;
    }
    .help-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
</style>

@endsection

@section('inner-content')
    <h2>Tạo Nhóm Mới</h2>

    <div class="alert alert-info">
        <strong>Lưu ý:</strong> Bạn cần có ít nhất {{ \App\Http\Controllers\Frontend\GroupFrontendController::REQUIRED_POINTS_TO_CREATE_GROUP }} điểm để tạo nhóm. 
        Điểm hiện tại của bạn: <strong>{{ auth()->user()->totalpoint }}</strong>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('group.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group mb-3">
            <label for="title" class="required">Tên Nhóm</label>
            <input type="text" class="form-control" id="title" name="title" required value="{{ old('title') }}">
            @error('title')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="type_code" class="required">Loại Nhóm</label>
            <select class="form-control" id="type_code" name="type_code" required>
                @foreach($groupTypes as $type)
                    <option value="{{ $type->type_code }}" {{ old('type_code') == $type->type_code ? 'selected' : '' }}>
                        {{ $type->title }}
                    </option>
                @endforeach
            </select>
            @error('type_code')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="description">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') }}</textarea>
            <div class="help-text">Mô tả về mục đích, nội quy và hoạt động của nhóm</div>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="photo">Ảnh đại diện nhóm</label>
            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
            <div class="help-text">Kích thước tối đa: 2MB. Định dạng: JPG, PNG, GIF</div>
            @error('photo')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="1" id="is_private" name="is_private" {{ old('is_private') ? 'checked' : '' }}>
            <label class="form-check-label" for="is_private">
                Nhóm riêng tư
            </label>
            <div class="help-text">Nếu chọn, chỉ thành viên được duyệt mới có thể xem nội dung nhóm</div>
        </div>

        <button type="submit" class="btn btn-primary">Tạo Nhóm</button>
    </form>

@endsection
