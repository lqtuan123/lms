 
@php
    use Carbon\Carbon;
@endphp
 
 
  
 
        @include('frontend_laydies.layouts.notification')
        <div class="notice-container">
            <h2>Thông Báo Của Bạn</h2>
            <ul>
                @foreach($notices as $notice)
                    @php
                        $createdAt = Carbon::parse($notice->created_at); // Thay đổi $comment thành đối tượng bạn đang sử dụng
                        $diffInMinutes = $createdAt->diffInMinutes();
                        $diffInHours = $createdAt->diffInHours();
                        $diffInDays = $createdAt->diffInDays();
                        $thoigian = "";
                        if ($diffInMinutes < 60) {
                            $thoigian= $diffInMinutes . ' phút trước';
                        } elseif ($diffInHours < 24) {
                            $thoigian= $diffInHours . ' tiếng trước';
                        } else {
                            $thoigian= $diffInDays . ' ngày trước';
                        }
                    @endphp
                    <li class="notice-item" onclick="markNotificationAsRead({{ $notice->id }}, '{{ $notice->url_view }}')">
                        <div class="notice-icon">🔔</div>
                        <div class="notice-content">
                            <h4>{{ $notice->title }}</h4>
                            <p>{{$thoigian}}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="col-12 mt-5 mb-5 d-flex justify-content-center">
                {{$notices->links('vendor.pagination.simple_itcctv')}}
            </div>
        </div>
 
 
<script>
    function markNotificationAsRead(notificationId, urlView) {
        // Gửi AJAX request để cập nhật trạng thái "đã xem"
        fetch(`/tnotice/mark-as-read/${notificationId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Chuyển hướng sau khi cập nhật thành công
                window.location.href = urlView;
            } else {
                alert(data.msg);
                // alert('Không thể cập nhật trạng thái thông báo.');
            }
        })
        .catch(error => console.error('Lỗi:', error));
    }
    </script>
    
 