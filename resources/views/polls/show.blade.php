@extends('frontend.layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $poll->title }}</span>
                    <div>
                        @if(auth()->id() == $poll->created_by)
                            <a href="{{ route('polls.edit', $poll->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <form method="POST" action="{{ route('polls.destroy', $poll->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa khảo sát này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        @endif
                        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5 class="card-title">{{ $poll->question }}</h5>
                    <p class="card-text text-muted">
                        <small>
                            Tạo bởi: {{ $poll->creator->name }} | 
                            {{ $poll->created_at->format('d/m/Y H:i') }}
                            @if($poll->expires_at)
                                | Hết hạn: {{ \Carbon\Carbon::parse($poll->expires_at)->format('d/m/Y H:i') }}
                            @endif
                        </small>
                    </p>

                    @if($poll->isExpired())
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Khảo sát này đã kết thúc
                        </div>
                    @endif

                    @if($hasVoted || $poll->isExpired())
                        <h6 class="mb-3">Kết quả ({{ $poll->getTotalVotesCount() }} phiếu bầu)</h6>
                        
                        @php 
                            $results = $poll->getVotesCountByOption();
                            $totalVotes = $poll->getTotalVotesCount(); 
                        @endphp

                        @foreach($results as $option)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $option['text'] }}</span>
                                    <span>{{ $option['count'] }} ({{ $option['percentage'] }}%)</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $option['percentage'] }}%" 
                                         aria-valuenow="{{ $option['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                
                                @if($hasVoted && $userVote->option_id == $option['id'])
                                    <div class="text-end">
                                        <small class="text-muted"><i class="fas fa-check-circle text-success"></i> Lựa chọn của bạn</small>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                    @else
                        <form method="POST" action="{{ route('polls.vote', $poll->id) }}">
                            @csrf
                            
                            @error('option_id')
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                            @enderror
                            
                            <div class="list-group mb-3">
                                @foreach($poll->options as $option)
                                    <label class="list-group-item">
                                        <input class="form-check-input me-1" type="radio" name="option_id" value="{{ $option->id }}">
                                        {{ $option->option_text }}
                                    </label>
                                @endforeach
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Bình chọn</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
