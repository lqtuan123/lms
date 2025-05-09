@extends('backend.layouts.master')
@section('css')
@endsection

@section('content')
<div class="content">
    <!-- BEGIN: Top Bar -->
    @include('backend.layouts.header')
    <!-- END: Top Bar -->
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Đặt lại mật khẩu cho người dùng
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">
                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">
                        Thông tin người dùng
                    </h2>
                </div>
                <div class="p-5">
                    <div class="flex flex-col xl:flex-row">
                        <div class="flex-1 mt-5 xl:mt-0">
                            <div class="grid grid-cols-12 gap-x-5">
                                <div class="col-span-12 xl:col-span-6">
                                    <div class="mt-3">
                                        <label for="name" class="form-label">Họ tên</label>
                                        <input id="name" type="text" class="form-control" value="{{ $user->full_name }}" disabled>
                                    </div>
                                </div>
                                <div class="col-span-12 xl:col-span-6">
                                    <div class="mt-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-x-5">
                                <div class="col-span-12 xl:col-span-6">
                                    <div class="mt-3">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input id="phone" type="text" class="form-control" value="{{ $user->phone }}" disabled>
                                    </div>
                                </div>
                                <div class="col-span-12 xl:col-span-6">
                                    <div class="mt-3">
                                        <label for="status" class="form-label">Trạng thái</label>
                                        <input id="status" type="text" class="form-control" value="{{ $user->status == 'active' ? 'Đang hoạt động' : 'Không hoạt động' }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">
                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">
                        Đặt lại mật khẩu
                    </h2>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('admin.user.process-reset-password', $user->id) }}">
                        @csrf
                        
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        
                        <div class="mt-3">
                            <label for="password" class="form-label">Mật khẩu mới</label>
                            <input id="password" type="password" name="password" class="form-control @error('password') border-danger @enderror" placeholder="Nhập mật khẩu mới">
                            @error('password')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu mới">
                        </div>
                        
                        <div class="mt-5 flex justify-end">
                            <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary w-24 mr-1">Hủy</a>
                            <button type="submit" class="btn btn-primary w-24">Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
@endsection 