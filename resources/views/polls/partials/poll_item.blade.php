<div class="list-group-item list-group-item-action">
    <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">{{ $poll->title }}</h5>
        <small class="text-muted">
            {{ $poll->created_at->diffForHumans() }}
        </small>
    </div>
    <p class="mb-1">{{ Str::limit($poll->question, 150) }}</p>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <small class="text-muted">
                <i class="fas fa-user"></i> {{ $poll->creator->name }}
                <i class="fas fa-chart-bar ms-2"></i> {{ $poll->getTotalVotesCount() }} phiếu bầu
                @if($poll->group)
                    <i class="fas fa-users ms-2"></i> {{ $poll->group->name }}
                @endif
            </small>
        </div>
        <div>
            @if($poll->hasUserVoted(auth()->id()))
                <span class="badge bg-success me-2">
                    <i class="fas fa-check"></i> Đã bình chọn
                </span>
            @endif
            
            @if($poll->isExpired())
                <span class="badge bg-secondary me-2">
                    <i class="fas fa-clock"></i> Đã kết thúc
                </span>
            @elseif($poll->expires_at)
                <span class="badge bg-warning text-dark me-2">
                    <i class="fas fa-clock"></i> Hết hạn: {{ \Carbon\Carbon::parse($poll->expires_at)->format('d/m/Y') }}
                </span>
            @endif
            
            <a href="{{ route('polls.show', $poll->id) }}" class="btn btn-sm btn-outline-primary">
                Xem chi tiết
            </a>
        </div>
    </div>
</div> 