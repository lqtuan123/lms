@extends('backend.layouts.master')

@section('content')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Báo cáo điểm</h2>
</div>

<div class="grid grid-cols-12 gap-6 mt-5">
    <!-- Tổng hợp theo hoạt động -->
    <div class="col-span-12 lg:col-span-6">
        <div class="intro-y box">
            <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">Điểm theo hoạt động</h2>
            </div>
            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Hoạt động</th>
                                <th class="whitespace-nowrap text-center">Số lượt</th>
                                <th class="whitespace-nowrap text-center">Tổng điểm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summaryByActivity as $activity)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $activity->name }}</div>
                                    <div class="text-slate-500 text-xs">{{ $activity->code }}</div>
                                </td>
                                <td class="text-center">{{ number_format($activity->total_transactions) }}</td>
                                <td class="text-center font-medium text-success">{{ number_format($activity->total_points) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 người dùng có điểm cao nhất -->
    <div class="col-span-12 lg:col-span-6">
        <div class="intro-y box">
            <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">Top 10 người dùng có điểm cao nhất</h2>
            </div>
            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">#</th>
                                <th class="whitespace-nowrap">Người dùng</th>
                                <th class="whitespace-nowrap text-center">Tổng điểm</th>
                                <th class="whitespace-nowrap text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topUsers as $key => $user)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>
                                    <div class="font-medium">{{ $user->full_name }}</div>
                                    <div class="text-slate-500 text-xs">{{ $user->email }}</div>
                                </td>
                                <td class="text-center font-medium text-primary">{{ number_format($user->totalpoint) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.user.points', $user->id) }}" class="btn btn-sm btn-primary">
                                        <i data-lucide="list" class="w-4 h-4 mr-1"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 