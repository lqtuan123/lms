@extends('backend.layouts.master')

@section('content')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Lịch sử điểm của {{ $user->full_name }}</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.user.index') }}" class="btn btn-warning shadow-md mr-2">
            <i data-lucide="corner-up-left" class="w-4 h-4 mr-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
        <div class="intro-y box p-5">
            <div class="flex flex-col sm:flex-row sm:items-center">
                <div class="flex items-center">
                    <div class="text-base font-medium">Tổng điểm hiện tại:</div>
                    <div class="ml-2 px-3 py-1 bg-success text-white rounded-md font-medium">{{ number_format($user->totalpoint) }} điểm</div>
                </div>
                <div class="sm:ml-auto mt-3 sm:mt-0">
                    <form action="{{ route('admin.user.recalculate-points', $user->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i> Tính lại điểm
                        </button>
                    </form>
                    <button type="button" class="btn btn-primary ml-2" data-tw-toggle="modal" data-tw-target="#add-points-modal">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Thêm điểm thủ công
                    </button>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="overflow-x-auto">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">#</th>
                            <th class="whitespace-nowrap">Loại hoạt động</th>
                            <th class="whitespace-nowrap">Mô tả</th>
                            <th class="whitespace-nowrap text-center">Điểm</th>
                            <th class="whitespace-nowrap text-center">Trạng thái</th>
                            <th class="whitespace-nowrap text-center">Thời gian</th>
                            <th class="whitespace-nowrap text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointHistory as $key => $history)
                        <tr>
                            <td>{{ $pointHistory->firstItem() + $key }}</td>
                            <td>
                                <div class="font-medium">{{ $history->pointRule->name }}</div>
                                <div class="text-slate-500 text-xs">{{ $history->pointRule->code }}</div>
                            </td>
                            <td>{{ $history->description }}</td>
                            <td class="text-center font-medium text-primary">{{ $history->point }}</td>
                            <td class="text-center">
                                @if($history->status == 'active')
                                <div class="flex items-center justify-center text-success">
                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> Kích hoạt
                                </div>
                                @else
                                <div class="flex items-center justify-center text-danger">
                                    <i data-lucide="x-square" class="w-4 h-4 mr-1"></i> Đã hủy
                                </div>
                                @endif
                            </td>
                            <td class="text-center">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @if($history->status == 'active')
                                <form action="{{ route('admin.points.cancel', $history->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn hủy giao dịch điểm này?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Không có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">
                {{ $pointHistory->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm điểm thủ công -->
<div id="add-points-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Thêm điểm cho người dùng</h2>
            </div>
            <form action="{{ route('admin.points.add-manual') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-body">
                    <div class="mt-3">
                        <label for="point_rule_id" class="form-label">Loại hoạt động</label>
                        <select id="point_rule_id" name="point_rule_id" class="form-select">
                            @foreach(App\Models\PointRule::where('status', 'active')->get() as $rule)
                            <option value="{{ $rule->id }}" data-points="{{ $rule->point_value }}">{{ $rule->name }} ({{ $rule->point_value }} điểm)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="description" class="form-label">Mô tả (tùy chọn)</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Nhập mô tả cho giao dịch điểm này"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Hủy</button>
                    <button type="submit" class="btn btn-primary w-20">Thêm điểm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 