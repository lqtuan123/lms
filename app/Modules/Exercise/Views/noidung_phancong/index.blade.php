@extends('backend.layouts.master')



@section('content')
<h1>Danh sách nội dung phân công</h1>
<a href="{{ route('admin.noidung_phancong.create') }}" class="btn btn-primary">Thêm mới</a>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Slug</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($noidungPhancongs as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ $item->slug }}</td>
            <td>
                <a href="{{ route('noidung_phancong.edit', $item->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                <form action="{{ route('noidung_phancong.destroy', $item->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận xóa?')">Xóa</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
