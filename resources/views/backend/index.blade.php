@extends('backend.layouts.master')
@section('css')
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
@section('content')

<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">Bảng điều khiển</h2>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <!-- Thống kê người dùng -->
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                <div class="report-box zoom-in stats-card">
                    <div class="box p-5">
                        <div class="flex">
                            <div class="stats-icon bg-primary/20 text-primary">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-auto">
                                <div class="report-box__indicator bg-success cursor-pointer tooltip" title="Tăng 12% so với tháng trước">
                                    12% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="text-3xl font-medium leading-8 mt-6">{{ \App\Models\User::where('status', 'active')->count() }}</div>
                        <div class="text-base text-slate-500 mt-1">Người dùng</div>
                    </div>
                </div>
            </div>
            
            <!-- Thống kê sách -->
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                <div class="report-box zoom-in stats-card">
                    <div class="box p-5">
                        <div class="flex">
                            <div class="stats-icon bg-warning/20 text-warning">
                                <i data-lucide="book-open" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-auto">
                                <div class="report-box__indicator bg-success cursor-pointer tooltip" title="Tăng 5% so với tháng trước">
                                    5% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="text-3xl font-medium leading-8 mt-6">
                            @php
                                try {
                                    $bookCount = \App\Modules\Book\Models\Book::where('status', 'active')->count();
                                    echo $bookCount;
                                } catch(\Exception $e) {
                                    echo 0;
                                }
                            @endphp
                        </div>
                        <div class="text-base text-slate-500 mt-1">Sách</div>
                    </div>
                </div>
            </div>
            
            <!-- Thống kê nhóm -->
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                <div class="report-box zoom-in stats-card">
                    <div class="box p-5">
                        <div class="flex">
                            <div class="stats-icon bg-info/20 text-info">
                                <i data-lucide="users-2" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-auto">
                                <div class="report-box__indicator bg-success cursor-pointer tooltip" title="Tăng 8% so với tháng trước">
                                    8% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="text-3xl font-medium leading-8 mt-6">
                            @php
                                try {
                                    $groupCount = \App\Modules\Group\Models\Group::where('status', 'active')->count();
                                    echo $groupCount;
                                } catch(\Exception $e) {
                                    echo 0;
                                }
                            @endphp
                        </div>
                        <div class="text-base text-slate-500 mt-1">Nhóm</div>
                    </div>
                </div>
            </div>
            
            <!-- Thống kê bài viết -->
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                <div class="report-box zoom-in stats-card">
                    <div class="box p-5">
                        <div class="flex">
                            <div class="stats-icon bg-success/20 text-success">
                                <i data-lucide="file-text" class="w-6 h-6"></i>
                            </div>
                            <div class="ml-auto">
                                <div class="report-box__indicator bg-success cursor-pointer tooltip" title="Tăng 15% so với tháng trước">
                                    15% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="text-3xl font-medium leading-8 mt-6">
                            @php
                                try {
                                    if (class_exists('\App\Modules\Tuongtac\Models\TBlog')) {
                                        $blogCount = \App\Modules\Tuongtac\Models\TBlog::where('status', '1')->count();
                                        echo $blogCount;
                                    } else {
                                        echo 0;
                                    }
                                } catch(\Exception $e) {
                                    echo 0;
                                }
                            @endphp
                        </div>
                        <div class="text-base text-slate-500 mt-1">Bài viết</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Biểu đồ sách theo danh mục -->
    <div class="col-span-12 lg:col-span-6 mt-8">
        <div class="intro-y block sm:flex items-center h-10">
            <h2 class="text-lg font-medium truncate mr-5">Sách theo danh mục</h2>
        </div>
        <div class="intro-y box p-5 mt-5">
            <canvas id="chart-books-by-category" height="300"></canvas>
        </div>
    </div>
    
    <!-- Biểu đồ người dùng theo điểm -->
    <div class="col-span-12 lg:col-span-6 mt-8">
        <div class="intro-y block sm:flex items-center h-10">
            <h2 class="text-lg font-medium truncate mr-5">Thống kê điểm người dùng</h2>
        </div>
        <div class="intro-y box p-5 mt-5">
            <canvas id="chart-users-by-points" height="300"></canvas>
        </div>
    </div>
    
    <!-- Top người dùng có điểm cao nhất -->
    <div class="col-span-12 lg:col-span-6 mt-8">
        <div class="intro-y block sm:flex items-center h-10">
            <h2 class="text-lg font-medium truncate mr-5">
                Top 10 người dùng điểm cao nhất
            </h2>
        </div>
        <div class="intro-y box p-5 mt-5">
            <div class="overflow-x-auto">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">#</th>
                            <th class="whitespace-nowrap">Người dùng</th>
                            <th class="text-center whitespace-nowrap">Tổng điểm</th>
                            <th class="text-center whitespace-nowrap">Tình trạng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            try {
                                $topUsers = \App\Models\User::where('status', 'active')
                                    ->orderBy('totalpoint', 'desc')
                                    ->limit(10)
                                    ->get();
                            } catch(\Exception $e) {
                                $topUsers = collect();
                            }
                        @endphp
                        
                        @forelse($topUsers as $key => $user)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="flex items-center">
                                    <div class="w-10 h-10 image-fit zoom-in">
                                        <img class="rounded-full" src="{{ $user->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}" alt="{{ $user->full_name }}">
                                    </div>
                                    <div class="ml-4">
                                        <a href="{{ route('admin.user.edit', $user->id) }}" class="font-medium whitespace-nowrap">{{ $user->full_name }}</a>
                                        <div class="text-slate-500 text-xs whitespace-nowrap">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center font-medium">{{ number_format($user->totalpoint ?? 0) }}</td>
                            <td class="w-40">
                                <div class="flex items-center justify-center text-success">
                                    <i data-lucide="check-square" class="w-4 h-4 mr-2"></i> Hoạt động
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-gray-500">Không có dữ liệu người dùng</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Hoạt động gần đây -->
    <div class="col-span-12 lg:col-span-6 mt-8">
        <div class="intro-y block sm:flex items-center h-10">
            <h2 class="text-lg font-medium truncate mr-5">
                Bài viết mới nhất
            </h2>
        </div>
        <div class="intro-y box p-5 mt-5">
            <div class="overflow-x-auto">
                @php
                    try {
                        if (class_exists('\App\Modules\Tuongtac\Models\TBlog')) {
                            $latestPosts = \App\Modules\Tuongtac\Models\TBlog::with('author')
                                ->where('status', '1')
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        } else {
                            $latestPosts = collect();
                        }
                    } catch(\Exception $e) {
                        $latestPosts = collect();
                    }
                @endphp
                
                <div class="space-y-4">
                    @forelse($latestPosts as $post)
                    <div class="border-b border-slate-200 pb-4 last:border-0 last:pb-0">
                        <div class="flex items-center">
                            <div class="w-10 h-10 flex-none image-fit mr-1">
                                <img alt="{{ $post->author->full_name ?? 'Người dùng' }}" class="rounded-full" src="{{ $post->author->photo ?? asset('backend/assets/dist/images/profile-6.jpg') }}">
                            </div>
                            <div class="ml-3 flex-1">
                                <a href="{{ route('admin.tblogs.edit', $post->id) }}" class="font-medium">{{ Str::limit($post->title, 60) }}</a>
                                <div class="text-slate-500 text-xs mt-0.5">
                                    {{ $post->author->full_name ?? 'Người dùng' }} · {{ $post->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            {{ Str::limit(strip_tags($post->content), 120) }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <div class="text-gray-500">Không có bài viết nào</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
 
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dữ liệu mặc định cho trường hợp API lỗi hoặc chưa có dữ liệu
        const defaultBookCategoryData = {
            labels: ['Chưa có dữ liệu'],
            datasets: [{
                data: [1],
                backgroundColor: ['rgba(200, 200, 200, 0.5)'],
                borderWidth: 1
            }]
        };
        
        const defaultUserPointsData = {
            labels: ['0-100', '101-500', '501-1000', '1001-2000', '2001-5000', '5000+'],
            datasets: [{
                label: 'Số lượng người dùng',
                data: [0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };
        
        // Khởi tạo biểu đồ sách theo danh mục với dữ liệu mặc định
        const bookCategoryCtx = document.getElementById('chart-books-by-category').getContext('2d');
        const bookCategoryChart = new Chart(bookCategoryCtx, {
            type: 'pie',
            data: defaultBookCategoryData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Số lượng sách theo danh mục'
                    }
                }
            }
        });
        
        // Khởi tạo biểu đồ người dùng theo điểm với dữ liệu mặc định
        const userPointsCtx = document.getElementById('chart-users-by-points').getContext('2d');
        const userPointsChart = new Chart(userPointsCtx, {
            type: 'bar',
            data: defaultUserPointsData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Phân bố người dùng theo điểm'
                    }
                }
            }
        });
        
        // Lấy dữ liệu thật từ API và cập nhật biểu đồ
        try {
            // Biểu đồ sách theo danh mục
            fetch('{{ route("admin.api.books.by.category") }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.length > 0) {
                        const colors = [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(199, 199, 199, 0.7)',
                            'rgba(83, 102, 255, 0.7)',
                            'rgba(40, 159, 64, 0.7)',
                            'rgba(210, 199, 199, 0.7)'
                        ];
                        
                        bookCategoryChart.data.labels = data.map(item => item.title);
                        bookCategoryChart.data.datasets[0].data = data.map(item => item.count);
                        bookCategoryChart.data.datasets[0].backgroundColor = colors.slice(0, data.length);
                        bookCategoryChart.update();
                    }
                })
                .catch(error => {
                    console.error('Error fetching book category data:', error);
                });
            
            // Biểu đồ người dùng theo điểm
            fetch('{{ route("admin.api.users.by.points") }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && Array.isArray(data) && data.length > 0) {
                        userPointsChart.data.datasets[0].data = data;
                        userPointsChart.update();
                    }
                })
                .catch(error => {
                    console.error('Error fetching user points data:', error);
                });
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    });
</script>
@endsection