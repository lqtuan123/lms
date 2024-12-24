@extends('backend.layouts.master')
@section ('scriptop')

<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>

@endsection
@section('content')
<div>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
           cập nhật grouprole
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <!-- BEGIN: Form Layout -->
            <form method="post" action="{{ route('admin.grouprole.update', $grouprole->id) }}">
                @csrf
                @method('PATCH')
                <div class="intro-y box p-5">
                    {{-- Hiển thị lỗi --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
            
                    {{-- Input Title --}}
                    <div class="mt-3">
                        <label for="title" class="form-label">Tiêu đề</label>
                        <input id="title" name="title" type="text" class="form-control" placeholder="vd: nhóm truyền thống" value="{{ old('title', $grouprole->title) }}">
                        @error('title')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            
                    {{-- Input Type Code --}}
                    <div class="mt-3">
                        <label for="type_code" class="form-label">Mã loại</label>
                        <input id="type_code" name="type_code" type="text" class="form-control" placeholder="default" value="{{ old('type_code', $grouprole->type_code) }}">
                        @error('type_code')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            
                    {{-- Select Status --}}
                    <div class="mt-3">
                        <label for="status" class="form-label">Tình trạng</label>
                        <select id="status" name="status" class="form-select mt-2">
                            <option value="active" {{ old('status', $grouprole->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $grouprole->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            
                    {{-- Submit Button --}}
                    <div class="text-right mt-5">
                        <button type="submit" class="btn btn-primary w-24">Cập nhật</button>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>
@endsection

@section ('scripts')

  
@endsection