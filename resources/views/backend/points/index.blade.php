@extends('backend.layouts.master')

@section('content')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Quy tắc tính điểm</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('admin.points.create') }}" class="btn btn-primary shadow-md mr-2">Thêm quy tắc mới</a>
    </div>
</div>

<div class="intro-y box p-5 mt-5">
    <div class="overflow-x-auto">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">#</th>
                    <th class="whitespace-nowrap">Tên</th>
                    <th class="whitespace-nowrap">Mã</th>
                    <th class="whitespace-nowrap">Mô tả</th>
                    <th class="whitespace-nowrap">Điểm</th>
                    <th class="whitespace-nowrap">Trạng thái</th>
                    <th class="whitespace-nowrap">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pointRules as $key => $rule)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $rule->name }}</td>
                    <td><span class="text-slate-500">{{ $rule->code }}</span></td>
                    <td>{{ $rule->description }}</td>
                    <td><span class="font-medium text-primary">{{ $rule->point_value }}</span></td>
                    <td>
                        @if($rule->status == 'active')
                        <div class="flex items-center text-success">
                            <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> Kích hoạt
                        </div>
                        @else
                        <div class="flex items-center text-danger">
                            <i data-lucide="x-square" class="w-4 h-4 mr-1"></i> Vô hiệu
                        </div>
                        @endif
                    </td>
                    <td>
                        <div class="flex">
                            <a href="{{ route('admin.points.edit', $rule->id) }}" class="btn btn-sm btn-primary mr-1">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 