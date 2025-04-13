@extends('backend.layouts.master')

@section('content')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Chi tiết bài viết</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.tblogs.edit', $blog->id) }}" class="btn btn-primary shadow-md mr-2">
                <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Chỉnh sửa
            </a>
            <a href="{{ route('admin.tblogs.index') }}" class="btn btn-secondary shadow-md">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="intro-y box mt-5">
        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
            <h2 class="font-medium text-base mr-auto">Thông tin bài viết</h2>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 lg:col-span-8">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Tiêu đề</td>
                                    <td>{{ $blog->title }}</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Slug</td>
                                    <td>{{ $blog->slug }}</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Tác giả</td>
                                    <td>{{ $blog->author->full_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Ngày tạo</td>
                                    <td>{{ $blog->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Cập nhật lần cuối</td>
                                    <td>{{ $blog->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Lượt xem</td>
                                    <td>{{ $blog->hit ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Trạng thái</td>
                                    <td>
                                        @if($blog->status == 1)
                                            <span class="px-2 py-1 bg-success text-white rounded">Hiển thị</span>
                                        @else
                                            <span class="px-2 py-1 bg-danger text-white rounded">Ẩn</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap font-medium">Tags</td>
                                    <td>
                                        @if(isset($blog->tags) && count($blog->tags) > 0)
                                            @foreach($blog->tags as $tag)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                                                    {{ $tag->title }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span>Không có tags</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-4">
                    <div class="border rounded p-5">
                        <div class="font-medium text-base mb-3">Hình ảnh</div>
                        @if($blog->photo)
                            @php
                                $photos = is_array($blog->photo) ? $blog->photo : json_decode($blog->photo, true);
                            @endphp
                            @if(is_array($photos) && count($photos) > 0)
                                <img src="{{ $photos[0] }}" alt="{{ $blog->title }}" class="w-full">
                            @elseif(is_string($blog->photo) && $blog->photo != '')
                                <img src="{{ $blog->photo }}" alt="{{ $blog->title }}" class="w-full">
                            @else
                                <p>Không có ảnh</p>
                            @endif
                        @else
                            <p>Không có ảnh</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <div class="font-medium text-base mb-3">Nội dung</div>
                <div class="border rounded p-5">
                    {!! $blog->content !!}
                </div>
            </div>

            @if(isset($blog->resource_files) && count($blog->resource_files) > 0)
                <div class="mt-6">
                    <div class="font-medium text-base mb-3">Tài liệu đính kèm</div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>Loại</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blog->resource_files as $resource)
                                    <tr>
                                        <td>
                                            @if(Str::contains($resource->url, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <img src="{{ asset($resource->url) }}" alt="{{ $resource->file_name }}" style="max-width: 100px; max-height: 100px;">
                                            @endif
                                            {{ $resource->file_name ?? 'N/A' }}
                                        </td>
                                        <td>{{ $resource->type_code ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ asset($resource->url) }}" target="_blank" class="btn btn-sm btn-primary">Xem/Tải xuống</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
@endsection 