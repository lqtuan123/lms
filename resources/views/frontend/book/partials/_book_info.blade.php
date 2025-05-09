<!-- Book main info section -->
<div class="book-main-info">
    <!-- Book cover -->
    <div class="book-cover-container">
        <?php $photos = explode(',', $book->photo); ?>
        <img src="{{ $photos[0] }}" alt="{{ $book->title }}" class="book-cover-img">

        <!-- Book actions buttons -->
        <div class="book-actions-container mt-3">
            <a href="{{ route('front.book.read', $book->id) }}" class="book-action-btn btn-read">
                <span class="btn-icon">📖</span>Đọc
            </a>
            @if (isset($book->has_audio) && $book->has_audio)
                <a href="{{ route('front.book.show', ['id' => $book->slug, 'format' => 'audio']) }}"
                    class="book-action-btn btn-bookmark" id="share-btn" data-book-url="{{ url()->current() }}">
                    <span class="btn-icon">🎧</span>Nghe
                </a>
            @endif
            <a id="bookmark-btn"
                class="book-action-btn btn-bookmark {{ \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($book->id, 'book') ? 'active' : '' }}"
                data-id="{{ $book->id }}" data-code="book">
                <span class="btn-icon">🤍</span>Thích
            </a>
            <a href="{{ route('front.tblogs.create') }}" class="book-action-btn btn-bookmark" id="share-btn"
                data-book-url="{{ url()->current() }}">
                <span class="btn-icon">🔗</span>Chia sẻ
            </a>
        </div>
    </div>

    <!-- Book info -->
    <div class="book-info-container">
        <h1 class="book-title">{{ $book->title }}</h1>

        <!-- Rating stars -->
        <div class="book-rating-container">
            <div class="book-rating-stars">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= floor($book->average_rating))
                        <span class="rating-star filled">★</span>
                    @elseif ($i - 0.5 <= $book->average_rating)
                        <span class="rating-star half">☆</span>
                    @else
                        <span class="rating-star">☆</span>
                    @endif
                @endfor
            </div>
            <span class="book-rating-text">
                {{ number_format($book->average_rating, 1) }} ({{ $book->rating_count }} đánh giá)
            </span>
        </div>

        <!-- Book metadata -->
        <div class="book-meta">
            <div class="book-meta-item">
                <span class="book-meta-label">Thể loại:</span>
                <span class="book-meta-value">{{ $book->bookType->title }}</span>
            </div>

            <div class="book-meta-item">
                <span class="book-meta-label">Tags:</span>
                <div class="book-tag-list">
                    @foreach ($tagNames as $tag)
                        <span class="book-tag">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Book description -->
        <h3 class="book-content-title">Giới thiệu sách</h3>
        <div class="book-description">
            <p>{!! nl2br(e($book->summary)) !!}</p>
        </div>

        <!-- Book main content bullet points -->
        <h3 class="book-content-title">Nội dung chính</h3>
        <ul class="book-content-list">
            @php
                // Tách nội dung chính từ $book->content thành danh sách bullet points
                $contentLines = explode("\n", $book->content);
                $bulletPoints = [];

                foreach ($contentLines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $bulletPoints[] = $line;
                    }
                }

                // Giới hạn số điểm hiển thị
                $bulletPoints = array_slice($bulletPoints, 0, 5);
            @endphp

            @foreach ($bulletPoints as $point)
                <li class="book-content-item">{{ $point }}</li>
            @endforeach
        </ul>
    </div>
</div> 