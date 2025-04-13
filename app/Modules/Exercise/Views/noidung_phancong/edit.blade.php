@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h1>Chỉnh sửa Nội dung Phân công</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.noidung_phancong.update', $noidungPhancong->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Chọn Phân công -->
        <div class="mb-3">
            <label for="phancong_id" class="form-label">Chọn Phân công</label>
            <select class="form-control" id="phancong_id" name="phancong_id" required>
                <option value="">-- Chọn phân công --</option>
                @foreach ($phancongs as $phancong)
                    <option value="{{ $phancong->id }}" {{ $noidungPhancong->phancong_id == $phancong->id ? 'selected' : '' }}>
                        {{ $phancong->giangvien_id }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tiêu đề -->
        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $noidungPhancong->title) }}" required>
        </div>

        <!-- Nội dung -->
        <div class="mb-3">
            <label for="content" class="form-label">Nội dung</label>
            <textarea class="form-control" id="content" name="content" rows="4">{{ old('content', $noidungPhancong->content) }}</textarea>
        </div>

        <!-- Giới hạn thời gian -->
        <div class="mb-3">
            <label for="time_limit" class="form-label">Giới hạn thời gian (phút)</label>
            <input type="number" class="form-control" id="time_limit" name="time_limit" value="{{ old('time_limit', $noidungPhancong->time_limit) }}">
        </div>

        <!-- Tài liệu (File Upload) -->
        <div class="mb-3">
            <label for="documents" class="form-label">Tài liệu (File)</label>
            <input id="documents" name="documents[]" type="file" class="form-control @error('documents') is-invalid @enderror" multiple>
            @error('documents')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Hiển thị tài nguyên đã tải lên -->
        @if ($noidungPhancong->resources)
    <div class="mt-3">
        <strong>Tài nguyên đã tải lên:</strong>
        <ul>
            @foreach (json_decode($noidungPhancong->resources)->resource_ids ?? [] as $resourceId)
                @php
                    $resource = \App\Modules\Resource\Models\Resource::find($resourceId);
                @endphp
                @if ($resource)
                    <li>
                        <a href="{{ asset($resource->url) }}" target="_blank">{{ $resource->file_name }}</a>
                        <a href="javascript:;" class="btn btn-danger btn-sm dltBtn" 
                           data-url="{{ route('noidung_phancong.resource.destroy', ['noidungPhancongId' => $noidungPhancong->id, 'resourceId' => $resource->id]) }}"
                           data-name="{{ $resource->file_name }}">
                           Xóa
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
@else
    <p class="mt-2 text-gray-600">Chưa có tài nguyên nào được tải lên.</p>
@endif


        <!-- Tags -->
        <div class="mt-3">
                    <label for="select-tags" class="form-label">Tags</label>
                    <select id="select-tags" name="tag_ids[]" multiple placeholder="..." autocomplete="off">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" {{ in_array($tag->id, $tag_ids) ? 'selected' : '' }}>{{ $tag->title }}</option>
                        @endforeach
                    </select>
                </div>




        <!-- Tự luận (JSON List) -->
        <div class="mb-3">
            <label for="tuluan" class="form-label">Tự luận (JSON List)</label>
            <textarea class="form-control" id="tuluan" name="tuluan" rows="3">{{ old('tuluan', json_encode($noidungPhancong->tuluan)) }}</textarea>
        </div>

        <!-- Trắc nghiệm (JSON List) -->
        <div class="mb-3">
            <label for="trachnghiem" class="form-label">Trắc nghiệm (JSON List)</label>
            <textarea class="form-control" id="trachnghiem" name="trachnghiem" rows="3">{{ old('trachnghiem', json_encode($noidungPhancong->trachnghiem)) }}</textarea>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('noidung_phancong.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/js/tom-select.complete.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('/js/css/tom-select.min.css') }}">

<script>
document.addEventListener("DOMContentLoaded", function () {
    new TomSelect("#select-tags", {
        create: true,
        placeholder: "Chọn tags...",
        persist: false,
        highlight: true,
        plugins: ['remove_button'],
        hideSelected: true,
        closeAfterSelect: false,
        allowEmptyOption: false,
        render: {
            no_results: function(data, escape) {
                return '<div class="no-results">Không tìm thấy tags phù hợp</div>';
            }
        },
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
});
</script>
@endsection
