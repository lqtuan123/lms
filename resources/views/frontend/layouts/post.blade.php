<?php
$posts = \App\Modules\Tuongtac\Models\TBlog::with(['author', 'tags'])->orderBy('created_at', 'desc')->paginate(12);
?>

<section id="latest-posts" class="padding-large" style="padding-top: 0 !important;">
    <div class="container">
        <div class="section-title d-md-flex justify-content-between align-items-center mb-4">
            <h3 class="d-flex align-items-center">Latest posts</h3>
            <a href="{{ route('front.tblogs.index') }}" class="btn">Xem tất cả</a>
        </div>
        <div class="row">
            @foreach ($posts->take(4) as $post)
                <?php
                    $images = json_decode($post->photo, true);
                    $image = $images[0] ?? 'images/default.jpg'; // fallback nếu không có ảnh
                    $link = route('front.tblogs.show', $post->slug);
                    $shortContent = \Illuminate\Support\Str::limit(strip_tags($post->content), 100); // tóm tắt nội dung
                ?>
                <div class="col-md-3 posts mb-4">
                    <img src="{{ $image }}" alt="{{ $post->title }}" class="img-fluid rounded-3">
                    <a href="{{ $link }}" class="fs-6 text-primary">
                        @if (count($post->tags))
                            {{ $post->tags->first()->title }}
                        @else
                            Chủ đề
                        @endif
                    </a>
                    <h4 class="card-title mb-2 text-capitalize text-dark">
                        <a href="{{ $link }}">{{ $post->title }}</a>
                    </h4>
                    <p class="mb-2">
                        {{ $shortContent }}
                        <span><a class="text-decoration-underline text-black-50" href="{{ $link }}">Đọc tiếp</a></span>
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
