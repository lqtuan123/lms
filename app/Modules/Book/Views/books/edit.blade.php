@extends('backend.layouts.master')

@section('scriptop')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/js/tom-select.complete.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/js/css/tom-select.min.css') }}">
@endsection

@section('content')
    <h2 class="intro-y text-lg font-medium mt-10">
        Chỉnh sửa sách
    </h2>

    <div class="mt-5">
        <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mt-3">
                <label>Tiêu đề</label>
                <input type="text" name="title" class="form-control" value="{{ $book->title }}" required>
            </div>

            <div class="mt-3">
                <label>Ảnh hiện tại</label>
                @if ($book->photo)
                    <img src="{{ $book->photo }}" alt="{{ $book->title }}"
                        style="width: 100px; height: auto;">
                @endif
                <input type="file" name="photo" class="form-control mt-2" accept="image/*">
            </div>

            <div class="mt-3">
                <label>Tóm tắt</label>
                <textarea name="summary" class="form-control">{{ $book->summary }}</textarea>
            </div>

            <div class="mt-3">
                <label>Nội dung</label>
                <textarea name="content" class="form-control">{{ $book->content }}</textarea>
            </div>

            <div class="mt-3">
                <label for="document" class="form-label">Tệp phương tiện (tùy chọn)</label>
                <input type="file" name="document[]" class="form-control" multiple>
                @if ($resources && count($resources) > 0)
                    <div class="mt-3">
                        <strong>Tệp đã tải lên:</strong>
                        <ul>
                            @foreach ($resources as $resource)
                                <li>
                                    <a href="{{ asset($resource->url) }}" target="_blank">
                                        {{ $resource->file_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="mt-2 text-gray-600">Chưa có tệp nào được chọn.</p>
                @endif
            </div>
            
            <div class="mt-3">
                <label style="min-width:70px" class="form-select-label" for="status">Tình trạng</label>
                <select name="status" class="form-select mt-2 sm:mr-2">
                    <option value="active" {{ $book->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $book->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="mt-3">
                <label for="post-form-4" class="form-label">Tags</label>
                <select id="select-junk" name="tag_ids[]" multiple placeholder=" ..." autocomplete="off">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" {{ in_array($tag->id, $tag_ids) ? 'selected' : '' }}>
                            {{ $tag->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        var select = new TomSelect('#select-junk', {
            maxItems: null,
            allowEmptyOption: true,
            plugins: ['remove_button'],
            sortField: {
                field: "text",
                direction: "asc"
            },
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
            },
            create: true
        });
        @if (count($tag_ids) == 0)
            select.clear();
        @endif
    </script>
@endsection
