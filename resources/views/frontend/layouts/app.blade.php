    <script src="{{ asset('assets/frontend/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/custom.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/summernote/summernote-lite.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/summernote/lang/summernote-vi-VN.js') }}"></script>

    <!-- Bookmark functionality -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const bookmarkButtons = document.querySelectorAll(".bookmark-btn");

            bookmarkButtons.forEach(function(btn) {
                btn.addEventListener("click", function(event) {
                    event.preventDefault();

                    const itemId = this.getAttribute("data-id");
                    const itemCode = this.getAttribute("data-code");
                    const heartIcon = this.querySelector("i.fa-heart") || this.querySelector("svg.wishlist");

                    // Hiệu ứng ngay lập tức cho cảm giác phản hồi nhanh
                    if (heartIcon) {
                        heartIcon.classList.toggle("text-danger");
                        heartIcon.classList.toggle("active");
                    }

                    fetch("{{ route('front.book.bookmark') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                item_id: itemId,
                                item_code: itemCode,
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (!data.success) {
                                // Redirect to login if not logged in
                                if (data.msg && data.msg.includes('đăng nhập')) {
                                    window.location.href = "{{ route('front.login') }}";
                                    return;
                                }
                            }
                            
                            if (heartIcon) {
                                if (!data.isBookmarked) {
                                    heartIcon.classList.remove("text-danger", "active");
                                } else {
                                    heartIcon.classList.add("text-danger", "active");
                                }
                            }
                        })
                        .catch((error) => {
                            console.error("Lỗi:", error);
                            // Revert lại nếu lỗi
                            if (heartIcon) {
                                heartIcon.classList.toggle("text-danger");
                                heartIcon.classList.toggle("active");
                            }
                        });
                });
            });
        });
    </script>

    @yield('scripts')
</body>

</html> 