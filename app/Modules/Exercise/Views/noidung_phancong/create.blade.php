@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h1>Thêm mới Nội dung Phân công</h1>
    
    <form action="{{ route('admin.noidung_phancong.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Chọn Phân công -->
        <div class="mb-3">
            <label for="phancong_id" class="form-label">Chọn Phân công</label>
            <select class="form-control" id="phancong_id" name="phancong_id" required>
                <option value="">-- Chọn phân công --</option>
                @foreach ($phancongs ?? [] as $phancong)
                    <option value="{{ $phancong->id }}">{{ $phancong->giangvien_id }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tiêu đề -->
        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <!-- Nội dung -->
        <div class="mb-3">
            <label for="content" class="form-label">Nội dung</label>
            <textarea class="form-control" id="content" name="content" rows="4"></textarea>
        </div>

        <!-- Giới hạn thời gian -->
        <div class="mb-3">
            <label for="time_limit" class="form-label">Giới hạn thời gian (phút)</label>
            <input type="number" class="form-control" id="time_limit" name="time_limit">
        </div>

        <!-- Tài liệu -->
        <div class="mb-3">
            <label for="documents" class="form-label">Tài liệu (File)</label>
            <input id="documents" name="documents[]" type="file" class="form-control" multiple>
        </div>

        <!-- Tags -->
        <div class="mb-3">
            <label for="select-tags" class="form-label">Tags</label>
            <select id="select-tags" name="tag_ids[]" multiple>
                @foreach ($tags ?? [] as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tự luận -->
        <div class="mb-3">
            <label for="tuluan" class="form-label">Chọn Bộ đề Tự luận</label>
            <select class="form-control" id="tuluan" name="tuluan[]" multiple>
                <option value="">-- Chọn bộ đề --</option>
                @foreach ($bode_tuluans ?? [] as $bode)
                    <option value="{{ $bode->id }}">{{ $bode->title }}</option>
                @endforeach
            </select>
        </div>


        <!-- Trắc nghiệm -->
        <div class="mb-3">
            <label for="tracnghiem" class="form-label">Chọn Bộ đề Trắc nghiệm</label>
            <select class="form-control" id="tracnghiem" name="tracnghiem[]" multiple>
                <option value="">-- Chọn bộ đề --</option>
                @foreach ($bode_tracnghiems ?? [] as $bode)
                    <option value="{{ $bode->id }}">{{ $bode->title }}</option>
                @endforeach
            </select>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary">Thêm mới</button>
    </form>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/js/tom-select.complete.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('/js/css/tom-select.min.css') }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
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
$(document).ready(function() {
        $('#tuluan, #tracnghiem').select2({
            placeholder: "-- Chọn bộ đề --",
            allowClear: true
        });
    });

</script>
@endsection
