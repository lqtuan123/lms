<?php
use Carbon\Carbon;
$user = auth()->user();
$notices= \App\Modules\Tuongtac\Models\TNotice::where('user_id',$user->id)->where('seen',1)->orderBy('id','desc')->limit(10)->get();
?>

<ul class="notification-list">
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
        <li onclick="markNotificationAsRead({{ $notice->id }}, '{{ $notice->url_view }}')">
           <span class='n_title'> {{ $notice->title }} </span><span class='n_time'>({{$thoigian}})</span>
        </li>
    @endforeach
</ul>
<script>
    function markNotificationAsRead(notificationId, urlView) {
        alert('aa');
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
    