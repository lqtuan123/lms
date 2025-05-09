    <!-- Scripts bổ sung -->
    <script>
        // Toggle bookmark và cập nhật UI
        function toggleBookmark(postId, itemCode) {
            fetch('{{ route('front.tblog.bookmark') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    item_id: postId,
                    item_code: itemCode
                })
            })
            .then(response => response.json())
            .then(data => {
                // Cập nhật UI
                const bookmarkBtn = document.getElementById(`bookmark-btn-${postId}`);
                const bookmarkIcon = bookmarkBtn.querySelector('i');
                
                if (data.status === 'added') {
                    bookmarkBtn.classList.add('text-red-500');
                    bookmarkIcon.classList.remove('far');
                    bookmarkIcon.classList.add('fas');
                } else {
                    bookmarkBtn.classList.remove('text-red-500');
                    bookmarkIcon.classList.remove('fas');
                    bookmarkIcon.classList.add('far');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Đã xảy ra lỗi khi yêu thích bài viết. Vui lòng thử lại sau.');
            });
        }
    </script>
    @yield('scripts')
</body> 