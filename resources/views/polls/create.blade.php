@extends('frontend.layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tạo khảo sát mới</div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('polls.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="title">Tiêu đề</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="question">Câu hỏi</label>
                            <textarea class="form-control @error('question') is-invalid @enderror" id="question" name="question" rows="3" required>{{ old('question') }}</textarea>
                            @error('question')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="group_id">Nhóm (Tùy chọn)</label>
                            <select class="form-control @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                                <option value="">-- Chọn nhóm --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="expires_at">Ngày hết hạn (Tùy chọn)</label>
                            <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                            @error('expires_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Các lựa chọn</span>
                                <button type="button" class="btn btn-sm btn-primary add-option-btn" id="add-option">
                                    <i class="fas fa-plus"></i> Thêm lựa chọn
                                </button>
                            </div>
                            <div class="card-body" id="options-container">
                                <div class="alert alert-info">Thêm từ 2 đến 5 lựa chọn.</div>
                                
                                <div class="form-group mb-2 option-input">
                                    <div class="input-group">
                                        <span class="input-group-text">1</span>
                                        <input type="text" class="form-control" name="options[]" required>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-2 option-input">
                                    <div class="input-group">
                                        <span class="input-group-text">2</span>
                                        <input type="text" class="form-control" name="options[]" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Tạo khảo sát</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let optionCount = 2;
        const maxOptions = 5;
        
        document.getElementById('add-option').addEventListener('click', function() {
            if (optionCount >= maxOptions) {
                alert('Bạn chỉ có thể thêm tối đa ' + maxOptions + ' lựa chọn');
                return;
            }
            
            optionCount++;
            
            const container = document.getElementById('options-container');
            const newOption = document.createElement('div');
            newOption.className = 'form-group mb-2 option-input';
            newOption.innerHTML = `
                <div class="input-group">
                    <span class="input-group-text">${optionCount}</span>
                    <input type="text" class="form-control" name="options[]" required>
                    <button type="button" class="btn btn-outline-danger remove-option">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(newOption);
            
            // Thêm sự kiện xóa option
            newOption.querySelector('.remove-option').addEventListener('click', function() {
                newOption.remove();
                optionCount--;
                // Cập nhật lại số thứ tự
                updateOptionNumbers();
            });
        });
        
        function updateOptionNumbers() {
            const options = document.querySelectorAll('.option-input');
            options.forEach((option, index) => {
                option.querySelector('.input-group-text').textContent = index + 1;
            });
        }
    });
</script>
@endsection 