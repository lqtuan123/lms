@extends('Tuongtac::frontend.blogs.body')

@section('topcss')
<link rel="stylesheet" href="{{ asset('frontend/assets_f/custom-group.css') }}">
@endsection

@section('inner-content')
    <h2>Chỉnh Sửa Nhóm</h2>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('group.update', $group->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="title" class="required">Tên Nhóm</label>
            <input type="text" class="form-control" id="title" name="title" required value="{{ old('title', $group->title) }}">
            @error('title')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="type_code" class="required">Loại Nhóm</label>
            <select class="form-control" id="type_code" name="type_code" required>
                @foreach($groupTypes as $type)
                    <option value="{{ $type->type_code }}" {{ old('type_code', $group->type_code) == $type->type_code ? 'selected' : '' }}>
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
            <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $group->description) }}</textarea>
            <div class="help-text">Mô tả về mục đích, nội quy và hoạt động của nhóm</div>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="photo">Ảnh đại diện nhóm</label>
            @if($group->photo)
                <div class="group-img mb-2">
                    <img src="{{ asset('storage/' . $group->photo) }}" alt="{{ $group->title }}" class="img-thumbnail" style="max-width: 200px;">
                </div>
            @endif
            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
            <div class="help-text">Kích thước tối đa: 2MB. Định dạng: JPG, PNG, GIF</div>
            @error('photo')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="1" id="is_private" name="is_private" {{ old('is_private', $group->is_private) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_private">
                Nhóm riêng tư
            </label>
            <div class="help-text">Nếu chọn, chỉ thành viên được duyệt mới có thể xem nội dung nhóm</div>
        </div>

        <button type="submit" class="btn btn-primary">Cập Nhật Nhóm</button>
    </form>

@endsection 