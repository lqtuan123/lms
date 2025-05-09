@extends('frontend.layouts.master')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Khảo sát</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('polls.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo khảo sát mới
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="pollTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="true">
                                Đang hoạt động
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="expired-tab" data-bs-toggle="tab" data-bs-target="#expired" type="button" role="tab" aria-controls="expired" aria-selected="false">
                                Đã kết thúc
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="my-polls-tab" data-bs-toggle="tab" data-bs-target="#my-polls" type="button" role="tab" aria-controls="my-polls" aria-selected="false">
                                Khảo sát của tôi
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="pollTabsContent">
                        <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                            @if($activePolls->count() > 0)
                                <div class="list-group">
                                    @foreach($activePolls as $poll)
                                        @include('polls.partials.poll_item', ['poll' => $poll])
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    {{ $activePolls->links() }}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Không có khảo sát nào đang hoạt động.
                                </div>
                            @endif
                        </div>
                        
                        <div class="tab-pane fade" id="expired" role="tabpanel" aria-labelledby="expired-tab">
                            @if($expiredPolls->count() > 0)
                                <div class="list-group">
                                    @foreach($expiredPolls as $poll)
                                        @include('polls.partials.poll_item', ['poll' => $poll])
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    {{ $expiredPolls->links() }}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Không có khảo sát nào đã kết thúc.
                                </div>
                            @endif
                        </div>
                        
                        <div class="tab-pane fade" id="my-polls" role="tabpanel" aria-labelledby="my-polls-tab">
                            @if($myPolls->count() > 0)
                                <div class="list-group">
                                    @foreach($myPolls as $poll)
                                        @include('polls.partials.poll_item', ['poll' => $poll])
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    {{ $myPolls->links() }}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Bạn chưa tạo khảo sát nào.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 