<?php
$groupPages = \App\Modules\Tuongtac\Models\TPage::where('item_code', 'group')->where('status', 'active')->latest()->take(6)->get();
?>

<section id="instagram" style="padding-top: 0 !important;">
    <div class="container">
      <div class="text-center mb-4">
        <h3>Nhóm nổi bật</h3>
      </div>
      <div class="row">
        @foreach ($groupPages as $page)
          <div class="col-md-2">
            <figure class="instagram-item position-relative rounded-3">
              <a href="{{ route('front.tpage.view', $page->slug) }}" class="image-link position-relative">
                <div class="icon-overlay position-absolute d-flex justify-content-center">
                    {{$page->title  }}
                </div>
                <img src="{{ $page->avatar ?? 'https://via.placeholder.com/150' }}" alt="{{ $page->title }}" class="img-fluid rounded-3 insta-image">
              </a>
            </figure>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  
 