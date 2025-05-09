@extends('frontend.layouts.master')

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5, #2563eb);
            --gold-gradient: linear-gradient(135deg, #ffd700, #f59e0b);
            --silver-gradient: linear-gradient(135deg, #e5e7eb, #9ca3af);
            --bronze-gradient: linear-gradient(135deg, #d97706, #92400e);
        }

        /* Base styles */
        .medal-icon {
            font-size: 1.5rem;
        }

        .first-place {
            color: #FFD700;
        }

        .second-place {
            color: #C0C0C0;
        }

        .third-place {
            color: #CD7F32;
        }

        /* Glassmorphism card styles */
        .glass-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }

        .leaderboard-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .leaderboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.8s;
        }

        .leaderboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .leaderboard-card:hover::before {
            transform: translateX(100%);
        }

        /* Tabs */
        .leaderboard-tabs {
            border-bottom: 2px solid rgba(243, 244, 246, 0.3);
            padding-bottom: 0;
        }

        .tab-button {
            position: relative;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .tab-button.active {
            color: #4f46e5;
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -1px;
            width: 100%;
            height: 2px;
            background: var(--primary-gradient);
        }

        /* Rank styles */
        .user-rank {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            color: white;
            filter: drop-shadow(0 4px 3px rgba(0, 0, 0, 0.07));
        }

        .rank-1 {
            background: var(--gold-gradient);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.4);
        }

        .rank-2 {
            background: var(--silver-gradient);
            box-shadow: 0 4px 15px rgba(192, 192, 192, 0.4);
        }

        .rank-3 {
            background: var(--bronze-gradient);
            box-shadow: 0 4px 15px rgba(205, 127, 50, 0.4);
        }

        .rank-other {
            background: linear-gradient(135deg, #64748b, #94a3b8);
            box-shadow: 0 2px 10px rgba(100, 116, 139, 0.2);
        }

        /* Banner */
        .trophy-banner {
            background: var(--primary-gradient);
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(31, 38, 135, 0.2);
        }

        .trophy-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        }

        .confetti-animation {
            animation: confetti-fall 3s linear infinite;
            position: absolute;
            opacity: 0.7;
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-10px) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            100% {
                transform: translateY(100px) rotate(90deg);
                opacity: 0;
            }
        }

        /* Podium */
        .podium-container {
            position: relative;
            height: 320px;
            margin-top: 2rem;
            margin-bottom: 3rem;
        }

        .podium {
            position: absolute;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            opacity: 0;
            transform: translateY(20px);
        }

        .podium:hover {
            transform: translateY(-10px) scale(1.03);
        }

        .podium-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            margin-bottom: 0.5rem;
            background-color: white;
            transition: all 0.3s ease;
        }

        .podium:hover .podium-avatar {
            transform: scale(1.1);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .podium-block {
            width: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        .podium-block::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 30%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.2), transparent);
        }

        .podium-name {
            font-weight: 600;
            font-size: 1rem;
            text-align: center;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            z-index: 1;
        }

        .podium-points {
            font-weight: 500;
            font-size: 0.9rem;
            z-index: 1;
        }

        .podium.first {
            height: 220px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
        }

        .podium.second {
            height: 180px;
            left: calc(50% - 130px);
            z-index: 2;
        }

        .podium.third {
            height: 150px;
            left: calc(50% + 130px);
            z-index: 1;
        }

        .podium.first .podium-block {
            background: var(--gold-gradient);
            height: 100%;
        }

        .podium.second .podium-block {
            background: var(--silver-gradient);
            height: 100%;
        }

        .podium.third .podium-block {
            background: var(--bronze-gradient);
            height: 100%;
        }

        .podium.first .podium-avatar {
            border-color: #FFD700;
        }

        .podium.second .podium-avatar {
            border-color: #C0C0C0;
        }

        .podium.third .podium-avatar {
            border-color: #CD7F32;
        }

        .podium-position {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .podium.first .podium-position {
            background: #FFD700;
        }

        .podium.second .podium-position {
            background: #C0C0C0;
        }

        .podium.third .podium-position {
            background: #CD7F32;
        }

        /* Point section */
        .point-card {
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .point-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .point-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')

    <div class=" ">
        <!-- Banner -->
        <div class="trophy-banner rounded-2xl mb-8 p-10 text-white text-center relative z-10">
            <h1 class="text-3xl md:text-5xl font-bold mb-4 tracking-tight">Bảng Vinh Danh Bạn Đọc</h1>
            <p class="text-lg opacity-90 max-w-2xl mx-auto leading-relaxed">Chúc mừng những bạn đọc đã tích lũy nhiều điểm
                nhất thông qua việc đọc sách, chia sẻ kiến thức và tham gia tích cực vào cộng đồng!</p>

            <!-- Ngẫu nhiên tạo hiệu ứng confetti -->
            @for ($i = 1; $i <= 20; $i++)
                <div class="confetti-animation text-2xl"
                    style="left: {{ rand(5, 95) }}%; 
                            top: {{ rand(-10, 40) }}px; 
                            animation-delay: {{ $i * 0.2 }}s;
                            animation-duration: {{ rand(3, 6) }}s;">
                    @php
                        $emoji = ['🏆', '✨', '🌟', '🎉', '📚', '📖', '🔥', '⭐'];
                        echo $emoji[array_rand($emoji)];
                    @endphp
                </div>
            @endfor
        </div>

        <!-- Leaderboard Tabs - Di chuyển lên trên -->
        <div class="glass-card rounded-xl shadow-xl overflow-hidden mb-8">
            <div class="leaderboard-tabs flex p-4 border-b border-gray-100">
                <button class="tab-button active flex-grow py-3 text-center text-lg" data-tab="all-time">
                    <i class="fas fa-trophy mr-2"></i> Tất cả thời gian
                </button>
                <button class="tab-button flex-grow py-3 text-center text-lg" data-tab="monthly">
                    <i class="fas fa-calendar-alt mr-2"></i> Tháng này
                </button>
                <button class="tab-button flex-grow py-3 text-center text-lg" data-tab="weekly">
                    <i class="fas fa-calendar-week mr-2"></i> Tuần này
                </button>
            </div>
        </div>

        <!-- Các tab content -->
        <div class="tab-content active" id="all-time-tab">
            <!-- Top 3 Reader Cards cho ALL TIME -->
            <div class="container mx-auto px-4 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <!-- #2 -->
                    @if (isset($topUsers[1]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300">
                            <div class="relative">
                                <div
                                    class="absolute top-3 left-3 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-bold text-sm">
                                    2</div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $topUsers[1]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $topUsers[1]->full_name }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $topUsers[1]->full_name }}</h3>
                                    {{-- <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($topUsers[1]->books_read_count ?? 0) }} cuốn</p> --}}
                                    <div class="mt-3 bg-gray-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-indigo-700 font-bold">{{ number_format($topUsers[1]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $topUsers[1]->id)
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- #1 -->
                    @if (isset($topUsers[0]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300 -mt-4">
                            <div class="relative">
                                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/6 w-10 h-10">
                                    <svg class="w-full h-full text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $topUsers[0]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $topUsers[0]->full_name }}"
                                        class="w-28 h-28 rounded-full object-cover border-4 border-yellow-400">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $topUsers[0]->full_name }}</h3>
                                    {{-- <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($topUsers[0]->books_read_count ?? 0) }} cuốn</p> --}}
                                    <div class="mt-3 bg-yellow-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-yellow-700 font-bold">{{ number_format($topUsers[0]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $topUsers[0]->id)
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full {{ $loop->first ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }} text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- #3 -->
                    @if (isset($topUsers[2]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300">
                            <div class="relative">
                                <div
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold text-sm">
                                    3</div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $topUsers[2]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $topUsers[2]->full_name }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $topUsers[2]->full_name }}</h3>
                                    {{-- <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($topUsers[2]->books_read_count ?? 0) }} cuốn</p> --}}
                                    <div class="mt-3 bg-amber-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-amber-700 font-bold">{{ number_format($topUsers[2]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $topUsers[2]->id)
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full {{ $loop->first ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }} text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Top Readers List -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-10">
                    <h2 class="text-xl font-bold mb-6">Top 10 người đọc nhiều nhất</h2>

                    <div class="space-y-4">
                        @foreach ($topUsers as $index => $user)
                            @if ($index > 2 && $index < 10)
                                <!-- Chỉ hiển thị từ vị trí 4 đến 10 -->
                                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-medium text-gray-700 mr-4">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex items-center">
                                            <img src="{{ $user->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                                alt="{{ $user->full_name }}"
                                                class="w-12 h-12 rounded-full object-cover mr-4">
                                            <div>
                                                <h3 class="font-medium">{{ $user->full_name }}</h3>
                                                {{-- <p class="text-gray-500 text-sm">Đọc
                                                    {{ number_format($user->books_read_count ?? 0) }} cuốn</p> --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-blue-600 font-bold">{{ number_format($user->total_points) }}
                                            điểm</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if (count($topUsers) <= 3)
                            <div class="text-center py-6 text-gray-500">
                                <p>Không có đủ dữ liệu để hiển thị top 10.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Hiển thị thêm thông tin hoạt động -->
            <div class="mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-2">Hoạt động tích cực:</p>
                <div class="flex flex-wrap gap-2">
                    @if(isset($topUsers[0]->activities['book']) && $topUsers[0]->activities['book'] > 0)
                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                            <i class="fas fa-book mr-1"></i> {{ $topUsers[0]->activities['book'] }} sách
                        </span>
                    @endif
                    @if(isset($topUsers[0]->activities['comment']) && $topUsers[0]->activities['comment'] > 0)
                        <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs">
                            <i class="fas fa-comment mr-1"></i> {{ $topUsers[0]->activities['comment'] }} bình luận
                        </span>
                    @endif
                    @if(isset($topUsers[0]->activities['post']) && $topUsers[0]->activities['post'] > 0)
                        <span class="px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs">
                            <i class="fas fa-pen mr-1"></i> {{ $topUsers[0]->activities['post'] }} bài viết
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-content hidden" id="monthly-tab">
            <!-- Top 3 Reader Cards cho MONTHLY -->
            <div class="container mx-auto px-4 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <!-- #2 -->
                    @if (isset($monthlyTopUsers[1]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300">
                            <div class="relative">
                                <div
                                    class="absolute top-3 left-3 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-bold text-sm">
                                    2</div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $monthlyTopUsers[1]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $monthlyTopUsers[1]->full_name }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $monthlyTopUsers[1]->full_name }}
                                    </h3>
                                    <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($monthlyTopUsers[1]->books_read_count ?? 0) }} cuốn</p>
                                    <div class="mt-3 bg-gray-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-indigo-700 font-bold">{{ number_format($monthlyTopUsers[1]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng trong tháng
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $monthlyTopUsers[1]->id)
                                                ->where('created_at', '>=', now()->subDays(30))
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- #1 -->
                    @if (isset($monthlyTopUsers[0]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300 -mt-4">
                            <div class="relative">
                                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/6 w-10 h-10">
                                    <svg class="w-full h-full text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $monthlyTopUsers[0]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $monthlyTopUsers[0]->full_name }}"
                                        class="w-28 h-28 rounded-full object-cover border-4 border-yellow-400">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $monthlyTopUsers[0]->full_name }}
                                    </h3>
                                    <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($monthlyTopUsers[0]->books_read_count ?? 0) }} cuốn</p>
                                    <div class="mt-3 bg-yellow-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-yellow-700 font-bold">{{ number_format($monthlyTopUsers[0]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng trong tháng
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $monthlyTopUsers[0]->id)
                                                ->where('created_at', '>=', now()->subDays(30))
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full {{ $loop->first ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }} text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- #3 -->
                    @if (isset($monthlyTopUsers[2]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300">
                            <div class="relative">
                                <div
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold text-sm">
                                    3</div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $monthlyTopUsers[2]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $monthlyTopUsers[2]->full_name }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $monthlyTopUsers[2]->full_name }}
                                    </h3>
                                    <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($monthlyTopUsers[2]->books_read_count ?? 0) }} cuốn</p>
                                    <div class="mt-3 bg-amber-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-amber-700 font-bold">{{ number_format($monthlyTopUsers[2]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $monthlyTopUsers[2]->id)
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full {{ $loop->first ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }} text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Top Readers List -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-10">
                    <h2 class="text-xl font-bold mb-6">Top 10 người đọc nhiều nhất trong tháng</h2>

                    <div class="space-y-4">
                        @foreach ($monthlyTopUsers as $index => $user)
                            @if ($index > 2 && $index < 10)
                                <!-- Chỉ hiển thị từ vị trí 4 đến 10 -->
                                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-medium text-gray-700 mr-4">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex items-center">
                                            <img src="{{ $user->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                                alt="{{ $user->full_name }}"
                                                class="w-12 h-12 rounded-full object-cover mr-4">
                                            <div>
                                                <h3 class="font-medium">{{ $user->full_name }}</h3>
                                                <p class="text-gray-500 text-sm">Đọc
                                                    {{ number_format($user->books_read_count ?? 0) }} cuốn</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-blue-600 font-bold">{{ number_format($user->total_points) }}
                                            điểm</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if (count($monthlyTopUsers) <= 3)
                            <div class="text-center py-6 text-gray-500">
                                <p>Không có đủ dữ liệu để hiển thị top 10 trong tháng.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Hiển thị thêm thông tin hoạt động -->
            <div class="mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-2">Hoạt động tích cực:</p>
                <div class="flex flex-wrap gap-2">
                    @if(isset($monthlyTopUsers[0]->activities['book']) && $monthlyTopUsers[0]->activities['book'] > 0)
                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                            <i class="fas fa-book mr-1"></i> {{ $monthlyTopUsers[0]->activities['book'] }} sách
                        </span>
                    @endif
                    @if(isset($monthlyTopUsers[0]->activities['comment']) && $monthlyTopUsers[0]->activities['comment'] > 0)
                        <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs">
                            <i class="fas fa-comment mr-1"></i> {{ $monthlyTopUsers[0]->activities['comment'] }} bình luận
                        </span>
                    @endif
                    @if(isset($monthlyTopUsers[0]->activities['post']) && $monthlyTopUsers[0]->activities['post'] > 0)
                        <span class="px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs">
                            <i class="fas fa-pen mr-1"></i> {{ $monthlyTopUsers[0]->activities['post'] }} bài viết
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-content hidden" id="weekly-tab">
            <!-- Top 3 Reader Cards cho WEEKLY -->
            <div class="container mx-auto px-4 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <!-- #2 -->
                    @if (isset($weeklyTopUsers[1]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300">
                            <div class="relative">
                                <div
                                    class="absolute top-3 left-3 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-bold text-sm">
                                    2</div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $weeklyTopUsers[1]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $weeklyTopUsers[1]->full_name }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $weeklyTopUsers[1]->full_name }}
                                    </h3>
                                    <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($weeklyTopUsers[1]->books_read_count ?? 0) }} cuốn</p>
                                    <div class="mt-3 bg-gray-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-indigo-700 font-bold">{{ number_format($weeklyTopUsers[1]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng trong tuần
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $weeklyTopUsers[1]->id)
                                                ->where('created_at', '>=', now()->subDays(7))
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- #1 -->
                    @if (isset($weeklyTopUsers[0]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300 -mt-4">
                            <div class="relative">
                                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/6 w-10 h-10">
                                    <svg class="w-full h-full text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $weeklyTopUsers[0]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $weeklyTopUsers[0]->full_name }}"
                                        class="w-28 h-28 rounded-full object-cover border-4 border-yellow-400">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $weeklyTopUsers[0]->full_name }}
                                    </h3>
                                    <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($weeklyTopUsers[0]->books_read_count ?? 0) }} cuốn</p>
                                    <div class="mt-3 bg-yellow-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-yellow-700 font-bold">{{ number_format($weeklyTopUsers[0]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng trong tuần
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $weeklyTopUsers[0]->id)
                                                ->where('created_at', '>=', now()->subDays(7))
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full {{ $loop->first ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }} text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- #3 -->
                    @if (isset($weeklyTopUsers[2]))
                        <div
                            class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:translate-y-[-10px] transition-all duration-300">
                            <div class="relative">
                                <div
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold text-sm">
                                    3</div>
                                <div class="flex flex-col items-center justify-center p-6 pt-10">
                                    <img src="{{ $weeklyTopUsers[2]->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                        alt="{{ $weeklyTopUsers[2]->full_name }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                    <h3 class="mt-4 text-xl font-bold text-center">{{ $weeklyTopUsers[2]->full_name }}
                                    </h3>
                                    <p class="text-blue-600 font-medium mt-1">Đọc
                                        {{ number_format($weeklyTopUsers[2]->books_read_count ?? 0) }} cuốn</p>
                                    <div class="mt-3 bg-amber-100 px-4 py-2 rounded-full">
                                        <span
                                            class="text-amber-700 font-bold">{{ number_format($weeklyTopUsers[2]->total_points) }}
                                            điểm</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                                        @php
                                            // Lấy sách đã đọc của người dùng
                                            $readBooks = DB::table('point_histories')
                                                ->where('user_id', $weeklyTopUsers[2]->id)
                                                ->where(function ($query) {
                                                    $query
                                                        ->where('reference_type', 'book')
                                                        ->orWhereExists(function ($subquery) {
                                                            $subquery
                                                                ->select(DB::raw(1))
                                                                ->from('point_rules')
                                                                ->whereRaw(
                                                                    'point_histories.point_rule_id = point_rules.id',
                                                                )
                                                                ->where('point_rules.code', 'read_book');
                                                        });
                                                })
                                                ->pluck('reference_id')
                                                ->toArray();

                                            // Lấy tag phổ biến nhất từ các sách đã đọc
                                            $popularTags = [];
                                            if (!empty($readBooks)) {
                                                $popularTags = DB::table('tag_books')
                                                    ->join('tags', 'tag_books.tag_id', '=', 'tags.id')
                                                    ->whereIn('tag_books.book_id', $readBooks)
                                                    ->select('tags.title', DB::raw('count(*) as count'))
                                                    ->groupBy('tags.title')
                                                    ->orderBy('count', 'desc')
                                                    ->limit(2)
                                                    ->get();
                                            }
                                        @endphp

                                        @foreach ($popularTags as $tag)
                                            <span
                                                class="px-2 py-1 rounded-full {{ $loop->first ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }} text-xs">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Top Readers List -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-10">
                    <h2 class="text-xl font-bold mb-6">Top 10 người đọc nhiều nhất trong tuần</h2>

                    <div class="space-y-4">
                        @foreach ($weeklyTopUsers as $index => $user)
                            @if ($index > 2 && $index < 10)
                                <!-- Chỉ hiển thị từ vị trí 4 đến 10 -->
                                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-medium text-gray-700 mr-4">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex items-center">
                                            <img src="{{ $user->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}"
                                                alt="{{ $user->full_name }}"
                                                class="w-12 h-12 rounded-full object-cover mr-4">
                                            <div>
                                                <h3 class="font-medium">{{ $user->full_name }}</h3>
                                                <p class="text-gray-500 text-sm">Đọc
                                                    {{ number_format($user->books_read_count ?? 0) }} cuốn</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-blue-600 font-bold">{{ number_format($user->total_points) }}
                                            điểm</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if (count($weeklyTopUsers) <= 3)
                            <div class="text-center py-6 text-gray-500">
                                <p>Không có đủ dữ liệu để hiển thị top 10 trong tuần.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Hiển thị thêm thông tin hoạt động -->
            <div class="mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-2">Hoạt động tích cực:</p>
                <div class="flex flex-wrap gap-2">
                    @if(isset($weeklyTopUsers[0]->activities['book']) && $weeklyTopUsers[0]->activities['book'] > 0)
                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                            <i class="fas fa-book mr-1"></i> {{ $weeklyTopUsers[0]->activities['book'] }} sách
                        </span>
                    @endif
                    @if(isset($weeklyTopUsers[0]->activities['comment']) && $weeklyTopUsers[0]->activities['comment'] > 0)
                        <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs">
                            <i class="fas fa-comment mr-1"></i> {{ $weeklyTopUsers[0]->activities['comment'] }} bình luận
                        </span>
                    @endif
                    @if(isset($weeklyTopUsers[0]->activities['post']) && $weeklyTopUsers[0]->activities['post'] > 0)
                        <span class="px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs">
                            <i class="fas fa-pen mr-1"></i> {{ $weeklyTopUsers[0]->activities['post'] }} bài viết
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Thông tin về cách tính điểm -->
        <div class="glass-card rounded-xl shadow-xl p-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-3"></i>
                Cách tích lũy điểm
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="point-card p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                    <div class="point-icon bg-blue-100 text-blue-600 text-2xl">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 text-xl mb-3">Đọc sách</h3>
                    <p class="text-gray-600 leading-relaxed">Nhận 1 điểm cho mỗi 5 phút đọc sách liên tục. Càng đọc nhiều,
                        càng tích lũy được nhiều điểm!</p>
                </div>

                <div class="point-card p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl">
                    <div class="point-icon bg-green-100 text-green-600 text-2xl">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 text-xl mb-3">Chia sẻ tài liệu</h3>
                    <p class="text-gray-600 leading-relaxed">Nhận 10 điểm khi đăng tải sách mới. Chia sẻ kiến thức, nhận
                        thêm điểm thưởng!</p>
                </div>

                <div class="point-card p-6 bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl">
                    <div class="point-icon bg-purple-100 text-purple-600 text-2xl">
                        <i class="fas fa-pen"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 text-xl mb-3">Hoạt động cộng đồng</h3>
                    <p class="text-gray-600 leading-relaxed">Nhận điểm khi viết bài, bình luận, thích bài viết và tương tác
                        với cộng đồng!</p>
                </div>
            </div>

            <div class="mt-8 p-6 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl">
                <div class="flex items-start">
                    <div class="point-icon bg-yellow-100 text-yellow-600 text-2xl flex-shrink-0">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800 text-xl mb-3">Phần thưởng cho người dẫn đầu</h3>
                        <p class="text-gray-600 leading-relaxed">Đang cập nhật!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.add('hidden'));

                    // Add active class to clicked button
                    button.classList.add('active');

                    // Show corresponding content
                    const tabId = button.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.remove('hidden');
                });
            });

            // Animation effects for leaderboard cards
            const leaderboardCards = document.querySelectorAll('.leaderboard-card');
            leaderboardCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.zIndex = '10';
                });

                card.addEventListener('mouseleave', () => {
                    card.style.zIndex = '1';
                });
            });

            // Animate cards when they appear
            function animateCards() {
                const cards = document.querySelectorAll('.tab-content.active .bg-white.rounded-xl');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.transition = 'all 0.5s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 100);
                    }, index * 150);
                });
            }

            // Initial animation
            animateCards();

            // Re-run animation when tab changes
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    setTimeout(animateCards, 100);
                });
            });
        });
    </script>
@endsection
