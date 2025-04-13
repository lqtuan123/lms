@extends('Tuongtac::frontend.blogs.body')

@section('inner-content')
<div class="container">
    <h1 class="title">🏆 Vinh danh thành viên xuất sắc</h1>

    <!-- Top 3 Thành viên -->
    <div class="top-3-container">
        @if(count($topUsers) > 1)
        <!-- Top 2 -->
        <div class="top-member rank-2">
            <div class="avatar-container">
                <img src="{{ $topUsers[1]->photo }}" alt="{{ $topUsers[1]->full_name }}" class="avatar top-2-avatar">
                <span class="rank-badge silver">🥈#2</span>
            </div>
            <h3>{{ $topUsers[1]->full_name }}</h3>
            <p class="point">Điểm: {{ $topUsers[1]->point }}</p>
        </div>
        @endif

        <!-- Top 1 -->
        <div class="top-member rank-1">
            <div class="avatar-container">
                <img src="{{ $topUsers[0]->photo }}" alt="{{ $topUsers[0]->full_name }}" class="avatar top-1-avatar">
                <span class="rank-badge gold">🏆#1</span>
            </div>
            <h3>{{ $topUsers[0]->full_name }}</h3>
            <p class="point">🔥 Điểm: {{ $topUsers[0]->point }}</p>
        </div>

        @if(count($topUsers) > 2)
        <!-- Top 3 -->
        <div class="top-member rank-3">
            <div class="avatar-container">
                <img src="{{ $topUsers[2]->photo }}" alt="{{ $topUsers[2]->full_name }}" class="avatar top-3-avatar">
                <span class="rank-badge bronze">🥉#3</span>
            </div>
            <h3>{{ $topUsers[2]->full_name }}</h3>
            <p class="point">Điểm: {{ $topUsers[2]->point }}</p>
        </div>
        @endif
    </div>

    <!-- Các thành viên khác -->
    <div class="all-users">
        <h2>🌟 Các thành viên khác</h2>
        <div class="grid-container">
            @foreach($otherUsers as $user)
            
            <div class="grid-item">
                <img src="{{ $user->photo }}" alt="{{ $user->full_name }}" class="avatar small">
                <h3>{{ $user->full_name }}</h3>
                <p>Điểm: <span class="point">{{ $user->point }}</span></p>
            </div>
         
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('botscript')
<style>
/* Container chung */
.container {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
    text-align: center;
}

.avatar-container {
    position: relative;
    display: inline-block;
}

/* Tiêu đề */
.title {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 40px;
}

/* ======= TOP 3 THÀNH VIÊN ======= */
.top-3-container {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 50px;
    text-align: center;
}

/* Thiết lập chiều cao cố định cho thẻ top thành viên */
.top-member {
    width: 180px;
    height: 200px; /* Đảm bảo chiều cao cố định */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background: white;
}

/* Top 1 nổi bật */
.rank-1 {
    transform: translateY(-20px);
    background: linear-gradient(135deg, #ffdd44, #ff9900);
    box-shadow: 0 5px 15px rgba(255, 165, 0, 0.5);
    padding: 20px;
    border-radius: 15px;
}

.rank-1 .avatar {
    width: 100px;
    height: 100px;
    border: 5px solid gold;
    animation: glow 1.5s infinite alternate;
}

/* Hiệu ứng phát sáng cho top 1 */
@keyframes glow {
    from {
        box-shadow: 0 0 10px gold;
    }
    to {
        box-shadow: 0 0 20px gold;
    }
}

/* Avatar */
.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
}

/* Avatar của top 2 và top 3 */
.top-2-avatar {
    border: 3px solid silver;
}

.top-3-avatar {
    border: 3px solid #cd7f32;
}

/* Thiết lập tên thành viên cố định với dấu ... nếu quá dài */
.top-member h3 {
    max-width: 160px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 1rem;
    margin-top: 5px;
}

/* Điểm số luôn nằm cách đáy 20px */
.point {
    font-weight: bold;
    color: #ff6600;
    margin-top: auto;
    margin-bottom: 20px;
}

/* Badge xếp hạng */
.rank-badge {
    position: absolute;
    top: 0;
    right: 0;
    font-size: 14px;
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 50px;
}

.gold {
    background: gold;
    color: black;
}

.silver {
    background: silver;
    color: black;
}

.bronze {
    background: #cd7f32;
    color: white;
}

/* ======= DANH SÁCH CÁC THÀNH VIÊN KHÁC ======= */
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    justify-content: center;
    margin-top: 10px;
}

/* Card thành viên */
.grid-item {
    background: white;
    width: 180px;
    height: 200px;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.grid-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

/* Avatar nhỏ */
.avatar.small {
    width: 60px;
    height: 60px;
}

/* Tên thành viên ngắn gọn */
.grid-item h3 {
    max-width: 130px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 1rem;
    margin-top: 5px;
}

/* Điểm số luôn cố định vị trí ở đáy */
.grid-item .point {
    margin-top: auto;
    margin-bottom: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .top-3-container {
        flex-direction: column;
        align-items: center;
    }
    
    .rank-1 {
        transform: translateY(0);
    }
}

</style>
@endsection
