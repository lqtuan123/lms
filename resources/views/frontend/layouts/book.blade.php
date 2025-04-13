<?php
// Get 5 random books
$featuredBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->inRandomOrder()
    ->limit(5)
    ->get();

// Get 5 newest books
$latestBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

// Get 5 most viewed books
$mostViewedBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->orderBy('views', 'desc')
    ->limit(5)
    ->get();

// Get 5 highest rated books
$highestRatedBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->get()
    ->map(function($book) {
        $voteItem = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $book->id)
            ->where('item_code', 'book')
            ->first();
        $book->rating = $voteItem ? $voteItem->point : 0;
        return $book;
    })
    ->sortByDesc('rating')
    ->take(5)
    ->values();

// Get top 3 categories with most books
$topCategories = \App\Modules\Book\Models\BookType::withCount(['books' => function($query) {
        $query->where('status', 'active')->where('block', 'no');
    }])
    ->where('status', 'active')
    ->orderBy('books_count', 'desc')
    ->limit(3)
    ->get();

$books = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->orderBy('id')
    ->limit(10)
    ->get();
?>
{{-- <style>
    .fixed-card-height {
        height: 460px;
        /* hoặc 100% nếu card cha có định khung */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .fixed-card-height img {
        height: 200px;
        object-fit: cover;
    }
</style> --}}


<section id="best-selling-items" class="position-relative padding-large">
    <div class="container">
        <div class="section-title d-md-flex justify-content-between align-items-center mb-4">
            <h3 class="d-flex align-items-center">Sách khuyên đọc</h3>
            <a href="{{ route('front.book.index') }}" class="btn">Xem tất cả</a>
        </div>

        <div class="position-absolute end-0 pe-0 pe-xxl-5 me-0 me-xxl-5 swiper-next main-slider-button-next">
            <svg class="chevron-forward-circle d-flex justify-content-center align-items-center p-2" width="80"
                height="80">
                <use xlink:href="#alt-arrow-right-outline"></use>
            </svg>
        </div>
        <div class="position-absolute start-0 ps-0 ps-xxl-5 ms-0 ms-xxl-5 swiper-prev main-slider-button-prev">
            <svg class="chevron-back-circle d-flex justify-content-center align-items-center p-2" width="80"
                height="80">
                <use xlink:href="#alt-arrow-left-outline"></use>
            </svg>
        </div>

        <div class="swiper product-swiper">
            <div class="swiper-wrapper">
                @foreach ($books as $book)
                    <div class="swiper-slide">
                        <div class="card position-relative p-4 border rounded-3">

                            <img src="{{ $book->photo }}" class="img-fluid shadow-sm" alt="product item">
                            <h6 class="mt-4 mb-0 fw-bold"><a href="{{ route('front.book.show', $book->slug) }}">{{ $book->title }}</a></h6>
                            <div class="review-content d-flex">
                                <p class="my-2 me-2 fs-6 text-black-50">
                                    {{ $book->user ? $book->user->full_name : 'Tác giả ẩn danh' }}</p>

                                <div class="rating text-warning d-flex align-items-center">
                                    @php
                                        $voteItem = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                                        $avgRating = $voteItem ? $voteItem->point : 0;
                                        $voteCount = $voteItem ? $voteItem->count : 0;
                                        $fullStars = floor($avgRating);
                                        $halfStar = $avgRating - $fullStars >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                    @endphp
                                    
                                    @for ($i = 0; $i < $fullStars; $i++)
                                        <svg class="star star-fill text-warning">
                                            <use xlink:href="#star-fill"></use>
                                        </svg>
                                    @endfor
                                    
                                    @if ($halfStar)
                                        <svg class="star star-half text-warning">
                                            <use xlink:href="#star-half"></use>
                                        </svg>
                                    @endif
                                    
                                    @for ($i = 0; $i < $emptyStars; $i++)
                                        <svg class="star star-empty text-secondary">
                                            <use xlink:href="#star-fill"></use>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            <span><i class="fa fa-eye"></i>
                                {{ $book->views }} lượt xem</span>
                            <div class="card-concern position-absolute start-0 end-0 d-flex gap-2">
                                <a href="{{ route('front.book.show', $book->slug) }}" class="btn btn-dark" title="Xem chi tiết">
                                    <svg class="book-open">
                                        <use xlink:href="#book-open"></use>
                                    </svg>
                                </a>
                                @php
                                    $isBookmarked = Auth::check() ? \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($book->id, 'book') : false;
                                @endphp
                                <a href="javascript:void(0)" class="btn btn-dark bookmark-btn" 
                                   data-id="{{ $book->id }}" data-code="book" title="Đánh dấu yêu thích">
                                    <svg class="wishlist {{ $isBookmarked ? 'text-danger active' : '' }}">
                                        <use xlink:href="#heart"></use>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>


<section id="limited-offer" class="padding-large"
    style="background-image: url(frontend/assets_f/images/banner-image-bg-1.jpg); background-size: cover; background-repeat: no-repeat; background-position: center; height: 800px;">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-md-6 text-center">
                <div class="image-holder">
                    <img src="{{ asset('frontend/assets_f/images/banner-image3.png') }}" class="img-fluid"
                        alt="banner">
                </div>
            </div>
            <div class="col-md-5 offset-md-1 mt-5 mt-md-0 text-center text-md-start">
                <h2>Sự kiện đọc sách sắp diễn ra!</h2>
                <p class="">Tham gia sự kiện đọc sách trực tuyến và khám phá những đầu sách mới đầy hấp dẫn.
                </p>

                <div id="countdown-clock"
                    class="text-dark d-flex align-items-center my-3 justify-content-center justify-content-md-start">
                    <div class="time d-grid pe-3">
                        <span class="days fs-1 fw-bold text-primary"></span>
                        <small>Ngày</small>
                    </div>
                    <span class="fs-1 text-secondary">:</span>
                    <div class="time d-grid pe-3 ps-3">
                        <span class="hours fs-1 fw-bold text-primary"></span>
                        <small>Giờ</small>
                    </div>
                    <span class="fs-1 text-secondary">:</span>
                    <div class="time d-grid pe-3 ps-3">
                        <span class="minutes fs-1 fw-bold text-primary"></span>
                        <small>Phút</small>
                    </div>
                    <span class="fs-1 text-secondary">:</span>
                    <div class="time d-grid ps-3">
                        <span class="seconds fs-1 fw-bold text-primary"></span>
                        <small>Giây</small>
                    </div>
                </div>

                <a href="" class="btn btn-outline-primary mt-3">Xem chi tiết sự
                    kiện</a>
            </div>

        </div>
    </div>
    </div>
</section>

<section id="items-listing" class="padding-large">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4 mb-lg-0 col-lg-3">
                <div class="featured border rounded-3 p-4">
                    <div class="section-title overflow-hidden mb-5 mt-2">
                        <h3 class="d-flex flex-column mb-0">Sách được đề cử</h3>
                    </div>
                    @foreach ($featuredBooks as $book)
                        <div class="items-lists">
                            <div class="item d-flex">
                                <img src="{{ $book->photo }}" class="img-fluid shadow-sm" alt="product item">
                                <div class="item-content ms-3">
                                    <h6 class="mb-0 fw-bold"><a href="{{ route('front.book.show', $book->slug) }}">{{ Str::limit($book->title, 30) }}</a></h6>
                                    <div class="review-content d-flex">
                                        <p class="my-2 me-2 fs-6 text-black-50">
                                            {{ Str::limit($book->user ? $book->user->full_name : 'Tác giả ẩn danh',10) }}
                                        </p>

                                        <div class="rating text-warning d-flex align-items-center">
                                            @php
                                                $voteItem = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                                                $avgRating = $voteItem ? $voteItem->point : 0;
                                                $fullStars = floor($avgRating);
                                                $halfStar = $avgRating - $fullStars >= 0.5;
                                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                            @endphp
                                            
                                            @for ($i = 0; $i < $fullStars; $i++)
                                                <svg class="star star-fill text-warning">
                                                    <use xlink:href="#star-fill"></use>
                                                </svg>
                                            @endfor
                                            
                                            @if ($halfStar)
                                                <svg class="star star-half text-warning">
                                                    <use xlink:href="#star-half"></use>
                                                </svg>
                                            @endif
                                            
                                            @for ($i = 0; $i < $emptyStars; $i++)
                                                <svg class="star star-empty text-secondary">
                                                    <use xlink:href="#star-fill"></use>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <span><i class="fa fa-eye"></i>
                                        {{ $book->views }} lượt xem</span>
                                    
                                    @php
                                        $isBookmarked = Auth::check() ? \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($book->id, 'book') : false;
                                    @endphp
                                    <a href="javascript:void(0)" class="bookmark-btn text-secondary ms-2" 
                                       data-id="{{ $book->id }}" data-code="book" title="Đánh dấu yêu thích">
                                        <i class="fa fa-heart {{ $isBookmarked ? 'text-danger active' : '' }}"></i>
                                    </a>
                                </div>
                            </div>
                            <hr class="gray-400">

                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6 mb-4 mb-lg-0 col-lg-3">
                <div class="latest-items border rounded-3 p-4">
                    <div class="section-title overflow-hidden mb-5 mt-2">
                        <h3 class="d-flex flex-column mb-0">Sách mới đăng</h3>
                    </div>
                    @foreach ($latestBooks as $book)
                    <div class="items-lists">
                        <div class="item d-flex">
                            <img src="{{ $book->photo }}" class="img-fluid shadow-sm" alt="product item">
                            <div class="item-content ms-3">
                                <h6 class="mb-0 fw-bold"><a href="{{ route('front.book.show', $book->slug) }}">{{ Str::limit($book->title, 30) }}</a></h6>
                                <div class="review-content d-flex">
                                    <p class="my-2 me-2 fs-6 text-black-50">
                                        {{ Str::limit($book->user ? $book->user->full_name : 'Tác giả ẩn danh', 10) }}
                                    </p>

                                    <div class="rating text-warning d-flex align-items-center">
                                        @php
                                            $voteItem = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                                            $avgRating = $voteItem ? $voteItem->point : 0;
                                            $fullStars = floor($avgRating);
                                            $halfStar = $avgRating - $fullStars >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        @endphp
                                        
                                        @for ($i = 0; $i < $fullStars; $i++)
                                            <svg class="star star-fill text-warning">
                                                <use xlink:href="#star-fill"></use>
                                            </svg>
                                        @endfor
                                        
                                        @if ($halfStar)
                                            <svg class="star star-half text-warning">
                                                <use xlink:href="#star-half"></use>
                                            </svg>
                                        @endif
                                        
                                        @for ($i = 0; $i < $emptyStars; $i++)
                                            <svg class="star star-empty text-secondary">
                                                <use xlink:href="#star-fill"></use>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <span><i class="fa fa-eye"></i>
                                    {{ $book->views }} lượt xem</span>
                                
                                @php
                                    $isBookmarked = Auth::check() ? \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($book->id, 'book') : false;
                                @endphp
                                <a href="javascript:void(0)" class="bookmark-btn text-secondary ms-2" 
                                   data-id="{{ $book->id }}" data-code="book" title="Đánh dấu yêu thích">
                                    <i class="fa fa-heart {{ $isBookmarked ? 'text-danger active' : '' }}"></i>
                                </a>
                            </div>
                        </div>
                        <hr class="gray-400">

                    </div>
                @endforeach
                </div>
            </div>
            <div class="col-md-6 mb-4 mb-lg-0 col-lg-3">
                <div class="best-reviewed border rounded-3 p-4">
                    <div class="section-title overflow-hidden mb-5 mt-2">
                        <h3 class="d-flex flex-column mb-0">Sách được đọc nhiều nhất</h3>
                    </div>
                    @foreach ($mostViewedBooks as $book)
                    <div class="items-lists">
                        <div class="item d-flex">
                            <img src="{{ $book->photo }}" class="img-fluid shadow-sm" alt="product item">
                            <div class="item-content ms-3">
                                <h6 class="mb-0 fw-bold"><a href="{{ route('front.book.show', $book->slug) }}">{{ Str::limit($book->title, 30) }}</a></h6>
                                <div class="review-content d-flex">
                                    <p class="my-2 me-2 fs-6 text-black-50">
                                        {{ Str::limit($book->user ? $book->user->full_name : 'Tác giả ẩn danh', 10) }}
                                    </p>

                                    <div class="rating text-warning d-flex align-items-center">
                                        @php
                                            $voteItem = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                                            $avgRating = $voteItem ? $voteItem->point : 0;
                                            $fullStars = floor($avgRating);
                                            $halfStar = $avgRating - $fullStars >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        @endphp
                                        
                                        @for ($i = 0; $i < $fullStars; $i++)
                                            <svg class="star star-fill text-warning">
                                                <use xlink:href="#star-fill"></use>
                                            </svg>
                                        @endfor
                                        
                                        @if ($halfStar)
                                            <svg class="star star-half text-warning">
                                                <use xlink:href="#star-half"></use>
                                            </svg>
                                        @endif
                                        
                                        @for ($i = 0; $i < $emptyStars; $i++)
                                            <svg class="star star-empty text-secondary">
                                                <use xlink:href="#star-fill"></use>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <span><i class="fa fa-eye"></i>
                                    {{ $book->views }} lượt xem</span>
                                
                                @php
                                    $isBookmarked = Auth::check() ? \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($book->id, 'book') : false;
                                @endphp
                                <a href="javascript:void(0)" class="bookmark-btn text-secondary ms-2" 
                                   data-id="{{ $book->id }}" data-code="book" title="Đánh dấu yêu thích">
                                    <i class="fa fa-heart {{ $isBookmarked ? 'text-danger active' : '' }}"></i>
                                </a>
                            </div>
                        </div>
                        <hr class="gray-400">

                    </div>
                @endforeach
                </div>
            </div>
            <div class="col-md-6 mb-4 mb-lg-0 col-lg-3">
                <div class="on-sale border rounded-3 p-4">
                    <div class="section-title overflow-hidden mb-5 mt-2">
                        <h3 class="d-flex flex-column mb-0">Sách có đánh giá cao nhất</h3>
                    </div>
                    @foreach ($highestRatedBooks as $book)
                    <div class="items-lists">
                        <div class="item d-flex">
                            <img src="{{ $book->photo }}" class="img-fluid shadow-sm" alt="product item">
                            <div class="item-content ms-3">
                                <h6 class="mb-0 fw-bold"><a href="{{ route('front.book.show', $book->slug) }}">{{ Str::limit($book->title, 30) }}</a></h6>
                                <div class="review-content d-flex">
                                    <p class="my-2 me-2 fs-6 text-black-50">
                                        {{ Str::limit($book->user ? $book->user->full_name : 'Tác giả ẩn danh', 10) }}
                                    </p>

                                    <div class="rating text-warning d-flex align-items-center">
                                        @php
                                            $voteItem = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                                            $avgRating = $voteItem ? $voteItem->point : 0;
                                            $fullStars = floor($avgRating);
                                            $halfStar = $avgRating - $fullStars >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        @endphp
                                        
                                        @for ($i = 0; $i < $fullStars; $i++)
                                            <svg class="star star-fill text-warning">
                                                <use xlink:href="#star-fill"></use>
                                            </svg>
                                        @endfor
                                        
                                        @if ($halfStar)
                                            <svg class="star star-half text-warning">
                                                <use xlink:href="#star-half"></use>
                                            </svg>
                                        @endif
                                        
                                        @for ($i = 0; $i < $emptyStars; $i++)
                                            <svg class="star star-empty text-secondary">
                                                <use xlink:href="#star-fill"></use>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <span><i class="fa fa-eye"></i>
                                    {{ $book->views }} lượt xem</span>
                                
                                @php
                                    $isBookmarked = Auth::check() ? \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($book->id, 'book') : false;
                                @endphp
                                <a href="javascript:void(0)" class="bookmark-btn text-secondary ms-2" 
                                   data-id="{{ $book->id }}" data-code="book" title="Đánh dấu yêu thích">
                                    <i class="fa fa-heart {{ $isBookmarked ? 'text-danger active' : '' }}"></i>
                                </a>
                            </div>
                        </div>
                        <hr class="gray-400">

                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section id="categories" class="padding-large pt-0">
    <div class="container">
      <div class="section-title overflow-hidden mb-4">
        <h3 class="d-flex align-items-center">Danh mục có nhiều sách nhất</h3>
      </div>
      <div class="row">
        @foreach($topCategories as $index => $category)
        <div class="col-md-4">
          <div class="card mb-4 border-0 rounded-3 position-relative">
            <a href="{{ route('front.book.byType', $category->slug) }}">
              <img src="{{ asset('frontend/assets_f/images/category'.($index+1).'.jpg')}}" class="img-fluid rounded-3" alt="category image">
              <h6 class="position-absolute bottom-0 bg-primary m-4 py-2 px-3 rounded-3">
                <a href="{{ route('front.book.byType', $category->slug) }}" class="text-white">
                  {{ $category->title }} ({{ $category->books_count }})
                </a>
              </h6>
            </a>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>
