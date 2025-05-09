@extends('frontend.layouts.master')

@section('title', 'Thông báo của bạn')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Thông báo của bạn</h1>
            
            @if($notifications->count() > 0)
            <button id="mark-all-read" class="text-sm px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                <i class="fas fa-check-double mr-1"></i> Đánh dấu tất cả đã đọc
            </button>
            @endif
        </div>
        
        <div id="notifications-list" class="divide-y divide-gray-200">
            @forelse($notifications as $notification)
                <div class="notification-item p-4 hover:bg-gray-50 transition-colors {{ $notification->seen == 1 ? 'bg-blue-50' : '' }}" data-id="{{ $notification->id }}">
                    <a href="{{ $notification->url_view }}" class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            @if($notification->seen == 1)
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            @else
                                <div class="w-3 h-3 border border-gray-300 rounded-full"></div>
                            @endif
                        </div>
                        <div class="flex-grow">
                            <p class="text-gray-800 font-medium mb-1">{!! $notification->title !!}</p>
                            <div class="flex items-center text-sm text-gray-500">
                                <span class="mr-2">
                                    <i class="far fa-clock mr-1"></i> 
                                    @formatTimeAgo($notification->created_at)
                                </span>
                                <span><i class="fas {{ $notification->item_code == 'tblog' ? 'fa-file-alt' : 'fa-bell' }} mr-1"></i> 
                                    {{ $notification->item_code == 'tblog' ? 'Bài viết' : 'Thông báo' }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-bell-slash text-5xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-700 mb-1">Không có thông báo nào</h3>
                    <p class="text-gray-500">Bạn sẽ nhận được thông báo khi có tương tác với bài viết của bạn</p>
                </div>
            @endforelse
        </div>
        
        @if($notifications->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Đánh dấu thông báo đã đọc khi click vào
        const notificationItems = document.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                
                // Gửi request AJAX để đánh dấu thông báo đã đọc
                fetch(`/notices/mark-as-read/${notificationId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove blue background and blue dot when notification is read
                        this.classList.remove('bg-blue-50');
                        const blueDot = this.querySelector('.bg-blue-500');
                        if (blueDot) {
                            blueDot.classList.remove('bg-blue-500');
                            blueDot.classList.add('border', 'border-gray-300');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
        
        // Đánh dấu tất cả thông báo đã đọc
        const markAllReadBtn = document.getElementById('mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                fetch('/notices/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật giao diện
                        document.querySelectorAll('.notification-item').forEach(item => {
                            item.classList.remove('bg-blue-50');
                            const blueDot = item.querySelector('.bg-blue-500');
                            if (blueDot) {
                                blueDot.classList.remove('bg-blue-500');
                                blueDot.classList.add('border', 'border-gray-300');
                            }
                        });
                        
                        // Cập nhật icon thông báo trên header
                        const notificationBadge = document.querySelector('#notification-count');
                        if (notificationBadge) {
                            notificationBadge.textContent = '0';
                            notificationBadge.classList.add('hidden');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }
    });
</script>
@endpush
