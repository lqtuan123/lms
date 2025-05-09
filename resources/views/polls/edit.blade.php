@extends('frontend.layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Chỉnh sửa khảo sát</div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('polls.update', $poll->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title">Tiêu đề</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $poll->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="question">Câu hỏi</label>
                            <textarea class="form-control @error('question') is-invalid @enderror" id="question" name="question" rows="3" required>{{ old('question', $poll->question) }}</textarea>
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
                                    <option value="{{ $group->id }}" {{ (old('group_id', $poll->group_id) == $group->id) ? 'selected' : '' }}>
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
                            <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" name="expires_at" value="{{ old('expires_at', ($poll->expires_at ? date('Y-m-d\TH:i', strtotime($poll->expires_at)) : '')) }}">
                            @error('expires_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Các lựa chọn</div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Lưu ý: Nếu khảo sát đã có người bình chọn, bạn không nên thay đổi nội dung các lựa chọn.
                                </div>
                                
                                @foreach($poll->options as $index => $option)
                                <div class="form-group mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $index + 1 }}</span>
                                        <input type="hidden" name="option_ids[]" value="{{ $option->id }}">
                                        <input type="text" class="form-control" name="options[]" value="{{ $option->option_text }}" required>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Cập nhật khảo sát</button>
                            <a href="{{ route('polls.show', $poll->id) }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 