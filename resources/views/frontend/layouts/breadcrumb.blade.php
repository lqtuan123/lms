<div class="bg-gray-100 py-3 xl:py-5 lg:py-5 md:py-5">
  <div class="container mx-auto">
      <div class="flex flex-wrap items-center justify-between">
          <!-- Tiêu đề trang -->
          <div>
              <h2 class="text-xl font-semibold text-gray-800">{{ $pagetitle ?? "" }}</h2>
          </div>

          <!-- Breadcrumb -->
          <nav aria-label="breadcrumb">
              <ol class="flex items-center space-x-2 text-gray-600">
                  <li>
                      <a href="{{ route('home') }}" class="text-blue-600 hover:underline">Trang chủ</a>
                  </li>
                  
                  @if (!empty($links))
                      @foreach ($links as $link)
                          <li class="flex items-center">
                              <span class="mx-1 text-gray-400">/</span>
                              @if ($link->url !== '#')
                                  <a href="{{ $link->url }}" class="text-blue-600 hover:underline">{{ $link->title }}</a>
                              @else
                                  <span>{{ $link->title }}</span>
                              @endif
                          </li>
                      @endforeach
                  @endif
              </ol>
          </nav>
      </div>
  </div>
</div>
