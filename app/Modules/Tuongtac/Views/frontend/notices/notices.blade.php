@if(count($notices) > 0)
    @foreach($notices as $notice)
        <a href="{{ $notice->url_view }}" class="notification-item block py-2 px-4 hover:bg-gray-50 border-b border-gray-100 transition-colors" data-id="{{ $notice->id }}">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    @php
                        $userPhoto = isset($notice->user_from) && $notice->user_from ? $notice->user_from->photo : null;
                    @endphp
                    
                    @if($userPhoto)
                        <div class="relative">
                            <img src="{{ $userPhoto }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                            @if($notice->seen == 1)
                                <div class="absolute -top-1 -right-1 w-2 h-2 bg-blue-500 rounded-full border-2 border-white"></div>
                            @endif
                        </div>
                    @else
                        <div class="relative">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-bell text-blue-500 text-xs"></i>
                            </div>
                            @if($notice->seen == 1)
                                <div class="absolute -top-1 -right-1 w-2 h-2 bg-blue-500 rounded-full border-2 border-white"></div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex-grow min-w-0">
                    @php
                        // Nhận biết tên người dùng trong thông báo
                        $title = $notice->title;
                        $userPattern = '/^(.*?) đã/';
                        preg_match($userPattern, $title, $matches);
                        $userName = isset($matches[1]) ? $matches[1] : '';
                        
                        // Xử lý nội dung thông báo để thay thế tên người dùng
                        if (!empty($userName)) {
                            $restOfTitle = str_replace($userName . ' đã', 'đã', $title);
                        } else {
                            $restOfTitle = $title;
                        }
                    @endphp

                    <p class="text-sm text-gray-800 mb-0 truncate">
                        @if(!empty($userName) && $userPhoto)
                            <span class="inline-flex items-center">
                                <img src="{{ $userPhoto }}" alt="Avatar" class="inline-block w-4 h-4 rounded-full object-cover mr-1">
                                <span class="font-medium">{{ $userName }}</span>
                            </span>
                            {{ $restOfTitle }}
                        @else
                            {{ $title }}
                        @endif
                    </p>
                    <span class="text-xs text-gray-500 block" style="font-size: 0.65rem;">@formatTimeAgo($notice->created_at)</span>
                </div>
            </div>
        </a>
    @endforeach
@else
    <div class="py-6 px-4 text-center">
        <div class="text-gray-400 mb-1">
            <i class="fas fa-bell-slash text-2xl"></i>
        </div>
        <p class="text-sm text-gray-500">Không có thông báo mới</p>
    </div>
@endif 