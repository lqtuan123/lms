<?php
 
$setting = \app\Models\SettingDetail::find(1);
$user = auth()->user();
 
// Get top categories with most books
$topCategories = \App\Modules\Book\Models\BookType::withCount(['books' => function($query) {
        $query->where('status', 'active')->where('block', 'no');
    }])
    ->where('status', 'active')
    ->orderBy('books_count', 'desc')
    ->limit(6)
    ->get();


?>


@extends('frontend.layouts.master1')

@section('css')
    {{-- <link rel="stylesheet" href="{{ asset('frontend/assets_f/style.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/custom8.css') }}" type="text/css" /> --}}
    <style>
        .leaderboard-user-card {
            transition: all 0.3s ease;
        }
        .leaderboard-user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        .user-rank {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            color: white;
        }
        .rank-1 {
            background: linear-gradient(45deg, #FFD700, #FFC107);
            box-shadow: 0 3px 8px rgba(255, 215, 0, 0.4);
        }
        .rank-2 {
            background: linear-gradient(45deg, #C0C0C0, #E0E0E0);
            box-shadow: 0 3px 6px rgba(192, 192, 192, 0.4);
        }
        .rank-3 {
            background: linear-gradient(45deg, #CD7F32, #D2691E);
            box-shadow: 0 3px 6px rgba(205, 127, 50, 0.4);
        }
        .rank-other {
            background: linear-gradient(45deg, #64748b, #94a3b8);
            box-shadow: 0 2px 4px rgba(100, 116, 139, 0.3);
        }

        /* Thiết kế cuộn riêng biệt cho Book và Aside */
        .book-aside-container {
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        @media (min-width: 1024px) {
            .book-aside-container {
                flex-direction: row;
            }
        }
        
        /* Phần nội dung chính (book) */
        .book-section {
            position: relative;
            overflow: visible;
            width: 100%;
        }
        
        /* Phần sidebar (aside) */
        .aside-section {
            width: 100%;
            position: relative;
        }
        
        @media (min-width: 1024px) {
            /* Trên màn hình lớn, book section chiếm 2/3 */
            .book-section {
                width: 66.666667%;
                flex: 0 0 66.666667%;
            }
            
            /* Trên màn hình lớn, aside section chiếm 1/3 và sticky */
            .aside-section {
                width: 33.333333%;
                flex: 0 0 33.333333%;
                position: sticky;
                top: 1rem;
                max-height: calc(100vh - 2rem);
                overflow-y: hidden;
                align-self: flex-start;
            }
            
            /* Chỉ hiển thị thanh cuộn khi hover vào aside */
            .aside-section:hover {
                overflow-y: auto;
            }
        }
        
        /* Tùy chỉnh thanh cuộn cho aside */
        .aside-section::-webkit-scrollbar {
            width: 6px;
        }
        
        .aside-section::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .aside-section::-webkit-scrollbar-thumb {
            background-color: rgba(203, 213, 225, 0.6);
            border-radius: 10px;
        }
        
        .aside-section:hover::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.8);
        }

        /* Hỗ trợ trình duyệt Firefox */
        .aside-section {
            scrollbar-width: thin;
            scrollbar-color: rgba(203, 213, 225, 0.6) transparent;
        }
    </style>
@endsection

@section('content')
    @include('frontend.layouts.bannertop')
    
        <!-- Book Categories Section - Full Width -->
        <section class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Thể loại sách</h2>
                <a href="{{ route('front.book.index') }}" class="text-blue-500 hover:text-blue-700 font-medium">Xem tất cả <i class="fas fa-chevron-right ml-1"></i></a>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($topCategories as $category)
                <a href="{{ route('front.book.byType', $category->slug) }}" class="category-card bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition cursor-pointer text-center">
                    <div class="bg-{{ ['blue', 'purple', 'pink', 'green', 'yellow', 'red'][rand(0,5)] }}-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-{{ ['book', 'brain', 'code', 'heart', 'square-root-alt', 'chart-line'][rand(0,5)] }} text-{{ ['blue', 'purple', 'pink', 'green', 'yellow', 'red'][rand(0,5)] }}-500 text-2xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-800">{{ $category->title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $category->books_count }} sách</p>
                </a>
                @endforeach
                
                @if(count($topCategories) < 6)
                    @for($i = count($topCategories); $i < 6; $i++)
                    <div class="category-card bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition cursor-pointer text-center">
                        <div class="bg-gray-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-book text-gray-500 text-2xl"></i>
                        </div>
                        <h3 class="font-medium text-gray-800">Thể loại khác</h3>
                        <p class="text-sm text-gray-500 mt-1">Khám phá</p>
                    </div>
                    @endfor
                @endif
            </div>
        </section>

        <!-- Book and Aside Sections -->
        <div class="book-aside-container gap-8">
            <div class="book-section pr-0 lg:pr-4">
                @include('frontend.layouts.book')
            </div>
            <div class="aside-section pl-0 lg:pl-4">
                @include('frontend.layouts.aside')
            </div>
        </div>
    
@endsection

@section('scripts')
    {{-- <script src="{{ asset('frontend/assets/js/timer.js') }}"></script> --}}
@endsection
