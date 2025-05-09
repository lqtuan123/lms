<?php
use Illuminate\Support\Str;
use App\Models\Tag;

// Get top tags
$toptags = Tag::where('status', 'active')
    ->orderBy('hit', 'desc')
    ->limit(5)
    ->get();

$ids = $toptags->pluck('id')->toArray();

// Query the menu tags
$menutags = Tag::where('status', 'active')
    ->whereNotIn('id', $ids)
    ->orderBy('hit', 'desc')
    ->limit(10)
    ->get();

// Query the remaining tags
$tags = Tag::where('status', 'active')
    ->whereNotIn('id', $ids)
    ->whereNotIn('id', $menutags->pluck('id')->toArray())
    ->orderBy('hit', 'desc')
    ->limit(50)
    ->get();

// Get new users
$newusers = \App\Models\User::orderBy('id', 'desc')->limit(5)->get();
?>

<div class="sidebar-section">
    <!-- Profile Card -->
    @auth
    <div class="sidebar-card mb-4">
        <div class="sidebar-card-body">
            <div class="flex items-center mb-3">
                <img src="{{ auth()->user()->photo ?? asset('images/default-avatar.png') }}" 
                    alt="Profile" class="rounded-full w-10 h-10 object-cover border-2 border-white shadow-sm">
                <div class="ml-3">
                    <h4 class="font-medium text-gray-800">{{ auth()->user()->full_name }}</h4>
                    <p class="text-xs text-gray-500">{{ '@' . Str::slug(auth()->user()->full_name, '') }}</p>
                </div>
            </div>
            <a href="{{ route('front.user.profile', auth()->id()) }}" 
                class="sidebar-link-button block w-full">
                <i class="fas fa-user-circle mr-2"></i> Xem trang cá nhân
            </a>
        </div>
    </div>
    @endauth
    
    <!-- Người dùng mới -->
    <div class="sidebar-card mb-4">
        <div class="sidebar-card-body">
            <h3 class="sidebar-heading">
                <i class="fas fa-user-friends mr-2 text-purple-500"></i>
                Người dùng mới
            </h3>

            <div class="space-y-3">
                @foreach ($newusers as $newuser)
                    <div class="user-item">
                        <div class="user-avatar">
                            <img src="{{ $newuser->photo }}" alt="{{ $newuser->full_name }}">
                        </div>
                        <div class="user-name">
                            <a href="{{ route('front.user.profile', $newuser->id) }}">
                                {{ $newuser->full_name }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('front.leaderboard') }}" class="view-all-link">Xem tất cả</a>
        </div>
    </div>
    
    <!-- Popular Tags -->
    <div class="sidebar-card">
        <div class="sidebar-card-body">
            <h3 class="sidebar-heading">
                <i class="fas fa-tags text-primary mr-2"></i>
                Tag phổ biến
            </h3>
            <div class="tags-wrap">
                @foreach($toptags as $tag)
                <a href="{{ route('front.tblogs.tag', $tag->slug) }}" 
                   class="sidebar-tag {{ request()->is('tblogs/tag/'.$tag->slug) ? 'active' : '' }}">
                    {{ $tag->title }}
                </a>
                @endforeach
                
                @foreach($menutags as $tag)
                <a href="{{ route('front.tblogs.tag', $tag->slug) }}" 
                   class="sidebar-tag {{ request()->is('tblogs/tag/'.$tag->slug) ? 'active' : '' }}">
                    {{ $tag->title }}
                </a>
                @endforeach
            </div>
            
            @if($tags->count() > 0)
            <div class="mt-3">
                <div class="text-sm text-gray-500 mb-2">Thẻ khác:</div>
                <div class="other-tags">
                    @foreach($tags as $tag)
                    <a href="{{ route('front.tblogs.tag', $tag->slug) }}" class="other-tag">
                        #{{ $tag->title }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .sidebar-section {
        display: flex;
        flex-direction: column;
    }
    
    .sidebar-card {
        background-color: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .sidebar-card-body {
        padding: 1rem;
    }
    
    .sidebar-heading {
        font-weight: 600;
        font-size: 1rem;
        color: #374151;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
    }
    
    .sidebar-link-button {
        padding: 0.5rem;
        border-radius: 0.375rem;
        text-align: center;
        background-color: #f3f4f6;
        color: #4b5563;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .sidebar-link-button:hover {
        background-color: #e5e7eb;
    }
    
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar-menu-item {
        display: flex;
        align-items: center;
        padding: 0.625rem 0.5rem;
        border-radius: 0.375rem;
        color: #4b5563;
        font-size: 0.9375rem;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease;
    }
    
    .sidebar-menu-item:hover {
        background-color: #f3f4f6;
    }
    
    .sidebar-menu-item.active {
        background-color: #e5e7eb;
        color: #2563eb;
        font-weight: 500;
    }
    
    .text-primary {
        color: #3b82f6;
    }
    
    .tags-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .sidebar-tag {
        display: inline-block;
        background-color: #f3f4f6;
        color: #4b5563;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    
    .sidebar-tag:hover {
        background-color: #e5e7eb;
        color: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .sidebar-tag.active {
        background-color: #dbeafe;
        color: #2563eb;
    }
    
    .other-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .other-tag {
        color: #3b82f6;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
        padding-bottom: 2px;
    }
    
    .other-tag::after {
        content: '';
        position: absolute;
        width: 0;
        height: 1px;
        bottom: 0;
        left: 0;
        background-color: #2563eb;
        transition: width 0.3s ease;
    }
    
    .other-tag:hover {
        color: #2563eb;
    }
    
    .other-tag:hover::after {
        width: 100%;
    }
    
    /* User styles */
    .user-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
        transition: transform 0.2s ease;
    }
    
    .user-item:hover {
        transform: translateX(3px);
    }
    
    .user-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    
    .user-avatar:hover {
        transform: scale(1.05);
    }
    
    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: filter 0.3s ease;
    }
    
    .user-avatar:hover img {
        filter: brightness(1.1);
    }
    
    .user-name {
        margin-left: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        transition: color 0.2s ease;
    }
    
    .user-name:hover {
        color: #2563eb;
    }
    
    .view-all-link {
        display: block;
        text-align: center;
        color: #3b82f6;
        font-size: 0.875rem;
        margin-top: 1rem;
        transition: all 0.3s ease;
        padding: 0.5rem;
        border-radius: 0.375rem;
    }
    
    .view-all-link:hover {
        color: #2563eb;
        background-color: #f3f4f6;
    }
</style>
