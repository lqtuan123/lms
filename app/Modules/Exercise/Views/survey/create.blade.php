@extends('backend.layouts.master')

@section('scriptop')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{ asset('js/js/tom-select.complete.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('js/css/tom-select.min.css') }}">
@endsection

@section('content')
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">
        Thêm Khảo Sát
    </h2>
</div>
<div class="grid grid-cols-12 gap-12 mt-5">
    <div class="intro-y col-span-12 lg:col-span-12">
        <!-- BEGIN: Form Layout -->
        <form method="post" action="{{ route('admin.survey.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="intro-y box p-5">
                <div class="mt-3">
                    <label for="title" class="form-label">Tiêu đề</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="hocphan_id" class="form-label">Học Phần</label>
                    <select name="hocphan_id" id="hocphan_id" class="form-select mt-2" required>
                        <option value="">Chọn học phần</option>
                        @foreach($hocPhanList as $hocphan)
                            <option value="{{ $hocphan->id }}" {{ old('hocphan_id') == $hocphan->id ? 'selected' : '' }}>
                                {{ $hocphan->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('hocphan_id')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="giangvien_id" class="form-label">Giảng Viên</label>
                    <select name="giangvien_id" id="giangvien_id" class="form-control" required>
                        <option value="">Chọn Giảng viên</option>
                        @foreach($teacherList as $giangvien)
                            <option value="{{ $giangvien->id }}" {{ old('giangvien_id') == $giangvien->id ? 'selected' : '' }}>
                                {{ $giangvien->user->full_name }}
                            </option>
                        @endforeach 
                    </select>
                    @error('giangvien_id')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Questions Section -->
                <div class="mt-5">
                    <label class="form-label">Danh sách câu hỏi</label>
                    <small class="text-gray-500">Thêm câu hỏi và chọn loại câu hỏi.</small>
                    <div id="questions-container" class="mt-3">
                        <!-- Template for new question -->
                        <div class="question-item mb-4 p-4 border rounded" style="background: #f9f9f9;">
                            <div class="flex justify-between items-center">
                                <h4>Câu hỏi</h4>
                                <button type="button" class="btn btn-danger btn-sm remove-question">Xóa</button>
                            </div>
                            <div class="mt-2">
                                <label class="form-label">Nội dung câu hỏi</label>
                                <input type="text" name="questions[0][question]" class="form-control question-text" required>
                            </div>
                            <div class="mt-2">
                                <label class="form-label">Loại câu hỏi</label>
                                <select name="questions[0][type]" class="form-select question-type" required>
                                    <option value="text">Văn bản</option>
                                    <option value="multiple_choice">Thang điểm 1–5</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-question" class="btn btn-outline-primary mt-3">Thêm câu hỏi</button>
                    @error('questions')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-right mt-5">
                    <a href="{{ route('admin.survey.index') }}" class="btn btn-outline-secondary w-24 mr-2">Hủy</a>
                    <button type="submit" class="btn btn-primary w-24">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize TomSelect for hocphan_id and giangvien_id
    new TomSelect('#hocphan_id', {
        sortField: { field: 'text', direction: 'asc' },
        placeholder: 'Chọn học phần...'
    });

    new TomSelect('#giangvien_id', {
        sortField: { field: 'text', direction: 'asc' },
        placeholder: 'Chọn giảng viên...'
    });

    // Dynamic question management
    let questionIndex = 1;

    document.getElementById('add-question').addEventListener('click', function () {
        const container = document.getElementById('questions-container');
        const newQuestion = document.createElement('div');
        newQuestion.className = 'question-item mb-4 p-4 border rounded';
        newQuestion.style.background = '#f9f9f9';
        newQuestion.innerHTML = `
            <div class="flex justify-between items-center">
                <h4>Câu hỏi</h4>
                <button type="button" class="btn btn-danger btn-sm remove-question">Xóa</button>
            </div>
            <div class="mt-2">
                <label class="form-label">Nội dung câu hỏi</label>
                <input type="text" name="questions[${questionIndex}][question]" class="form-control question-text" required>
            </div>
            <div class="mt-2">
                <label class="form-label">Loại câu hỏi</label>
                <select name="questions[${questionIndex}][type]" class="form-select question-type" required>
                    <option value="text">Văn bản</option>
                    <option value="multiple_choice">Thang điểm 1–5</option>
                </select>
            </div>
        `;
        container.appendChild(newQuestion);
        questionIndex++;
        attachQuestionEventListeners();
    });

    function attachQuestionEventListeners() {
        // Remove question
        document.querySelectorAll('.remove-question').forEach(button => {
            button.addEventListener('click', function () {
                if (document.querySelectorAll('.question-item').length > 1) {
                    this.closest('.question-item').remove();
                } else {
                    alert('Phải có ít nhất một câu hỏi.');
                }
            });
        });
    }

    // Initial event listeners
    attachQuestionEventListeners();
</script>
@endsection