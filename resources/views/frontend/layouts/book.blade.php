<?php
$books = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->orderBy('id')
    ->limit(4)
    ->get();
?>
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Lưới hiển thị sách */
    .books-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        /* 8 cột trên màn hình lớn */
        gap: 12px;
    }

    /* Card sách */
    .book-card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        height: 250px;
        /* Giảm chiều cao của card */
        display: flex;
        flex-direction: column;
    }

    .book-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* Ảnh sách */
    .book-image img {
        width: 100%;
        height: 120px;
        /* Giảm chiều cao ảnh */
        object-fit: cover;
    }

    /* Nội dung sách */
    .book-content {
        padding: 8px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Giới hạn độ dài tiêu đề sách */
    .book-title {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
        text-decoration: none;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        /* Giới hạn 1 dòng */
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }

    .book-title:hover {
        color: #007bff;
    }

    /* Badge loại sách */
    .custom-badge {
        display: inline-block;
        background-color: #007bff;
        color: #fff;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 4px;
        align-self: flex-start;
    }

    /* Thông tin tác giả */
    .book-meta {
        font-size: 12px;
        color: #666;
        margin-top: auto;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .books-grid {
            grid-template-columns: repeat(6, 1fr);
            /* 6 cột */
        }
    }

    @media (max-width: 992px) {
        .books-grid {
            grid-template-columns: repeat(4, 1fr);
            /* 4 cột */
        }
    }

    @media (max-width: 768px) {
        .books-grid {
            grid-template-columns: repeat(2, 1fr);
            /* 2 cột */
        }
    }

    @media (max-width: 480px) {
        .books-grid {
            grid-template-columns: repeat(1, 1fr);
            /* 1 cột */
        }
    }
</style>
<!-- Danh sách sách -->
<<div class="container">
    <h2 class="h4 font-weight-bold mb-4">Sách</h2>
    <div class="books-grid">
        @foreach ($books as $book)
            <div class="book-card">
                <div class="book-image">
                    <a href="{{ route('front.book.show', $book->slug) }}">
                        <img src="{{ $book->photo }}" alt="{{ $book->title }}">
                    </a>
                </div>
                <div class="book-content">
                    <a href="{{ route('front.book.show', $book->slug) }}" class="book-title">
                        {{ $book->title }}
                    </a>
                    <span class="custom-badge">
                        {{ $book->bookType ? $book->bookType->title : 'N/A' }}
                    </span>
                    <div class="book-meta">
                        <span><i class="fa fa-user"></i> {{ $book->user ? $book->user->full_name : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    </div>

    </div>
