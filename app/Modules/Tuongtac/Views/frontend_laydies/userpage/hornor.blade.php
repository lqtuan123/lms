@extends('Tuongtac::frontend_laydies.blogs.body')
@section('inner-content')
<div class="container">
    <h1 class="title">Vinh danh thành viên xuất sắc</h1>

    <!-- Top 10 Thành viên -->
    <div class="top-users">
        <h2>Top 10 Thành viên</h2>
        <div class="grid-container">
            @foreach($topUsers as $user)
            <div class="grid-item top-member">
                <img src="{{ $user->photo }}" alt="{{ $user->full_name }}" class="avatar">
                <h3>{{ $user->full_name }}</h3>
                <p>Điểm: {{ $user->point }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Các thành viên khác -->
    <div class="all-users">
        <h2>Các thành viên khác</h2>
        <div class="grid-container">
            @foreach($otherUsers as $user)
            <div class="grid-item">
                <img src="{{ $user->photo }}" alt="{{ $user->full_name }}" class="avatar">
                <h3>{{ $user->full_name }}</h3>
                <p>Điểm: {{ $user->point}}</p>
            </div>
            @endforeach
        </div>
    </div>
    
</div>
@endsection


@section('botscript')
@endsection