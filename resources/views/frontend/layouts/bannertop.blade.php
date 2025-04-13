<?php
// Lấy 3 cuốn sách bất kỳ
$randomBooks = \App\Modules\Book\Models\Book::with(['user', 'bookType'])
    ->where('status', 'active')
    ->where('block', 'no')
    ->inRandomOrder()
    ->limit(3)
    ->get();
?>
<section id="billboard" class="position-relative d-flex align-items-center py-5 bg-light-gray"
      style="background-image: url(frontend/assets_f/images/banner-image-bg.jpg); background-size: cover; background-repeat: no-repeat; background-position: center; height: 800px;">
      <div class="position-absolute end-0 pe-0 pe-xxl-5 me-0 me-xxl-5 swiper-next main-slider-button-next">
        <svg class="chevron-forward-circle d-flex justify-content-center align-items-center p-2" width="80" height="80">
          <use xlink:href="#alt-arrow-right-outline"></use>
        </svg>
      </div>
      <div class="position-absolute start-0 ps-0 ps-xxl-5 ms-0 ms-xxl-5 swiper-prev main-slider-button-prev">
        <svg class="chevron-back-circle d-flex justify-content-center align-items-center p-2" width="80" height="80">
          <use xlink:href="#alt-arrow-left-outline"></use>
        </svg>
      </div>
      <div class="swiper main-swiper">
        <div class="swiper-wrapper d-flex align-items-center">
          @foreach($randomBooks as $index => $book)
          <div class="swiper-slide">
            <div class="container">
              <div class="row d-flex flex-column-reverse flex-md-row align-items-center">
                <div class="col-md-5 offset-md-1 mt-5 mt-md-0 text-center text-md-start">
                  <div class="banner-content">
                    <h2>{{ $book->title }}</h2>
                    <p>{{ Str::limit($book->summary, 100) }}</p>
                    <a href="{{ route('front.book.show', $book->slug) }}" class="btn mt-3">Đọc sách</a>
                  </div>
                </div>
                <div class="col-md-6 text-center">
                  <div class="image-holder">
                    <img src="{{ $book->photo }}" class="img-fluid" alt="{{ $book->title }}" style="max-height: 400px; object-fit: contain;">
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </section>
    </section>