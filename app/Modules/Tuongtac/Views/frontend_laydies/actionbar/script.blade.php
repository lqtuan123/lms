<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ratings = document.querySelectorAll('.rating-container ');
        ratings.forEach(rating => {
            const stars = rating.querySelectorAll('.star');

            stars.forEach(star => {
                // Hover chỉ để hiển thị tạm thời
                star.addEventListener('mouseover', function () {
                    stars.forEach(s => s.classList.remove('hover')); // Xóa hover cũ
                    this.classList.add('hover');
                    let sibling = this.nextElementSibling;
                    while (sibling) {
                        sibling.classList.add('hover');
                        sibling = sibling.nextElementSibling;
                    }
                });

                // Rời chuột thì xóa hover
                star.addEventListener('mouseout', function () {
                    stars.forEach(s => s.classList.remove('hover'));
                });

                // Click để chọn số sao
                star.addEventListener('click', function () {
                    const value = this.getAttribute('data-value'); // Lấy số sao
                    const postId = this.closest('.rating-container').getAttribute('data-post-id'); // Lấy ID bài viết
                    const item_code=  this.closest('.rating-container').getAttribute('item_code');
                    fetch(`{{route("front.votes.vote")}}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ point: value,item_code:item_code,item_id:postId })
                    })
                    .then(response => response.json())
                        .then(data => {
                           
                            if (data.success) {
                                // Cập nhật điểm trung bình và số lượt vote
                                // document.getElementById('average-point').textContent = data.averagePoint.toFixed(1);
                                document.getElementById('vote-count-'+postId).innerText = data.count;

                                // Gán class selected cho các ngôi sao
                                stars.forEach(s => s.classList.remove('selected')); // Xóa selected cũ
                                this.classList.add('selected');
                                let sibling = this.nextElementSibling;
                                while (sibling) {
                                    sibling.classList.add('selected');
                                    sibling = sibling.nextElementSibling;
                                }
                            }
                            else
                            {
                                // alert(data.msg);
                                const loginPopup = document.getElementById('loginPopup');
                                loginPopup.style.display = 'flex';
                                const loginForm = document.getElementById('ajaxLoginForm');
                                loginForm.addEventListener('submit', function (e) {
                                    e.preventDefault(); // Ngăn form gửi thông thường
                            
                                    const formData = new FormData(loginForm);
                                    fetch('{{ route('ajax.login') }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        },
                                        body: formData,
                                    })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === 'success') {
                                                loginPopup.style.display = 'none';
                                                location.reload();
                                            
                                            } else {
                                                loginError.style.display = 'block';
                                                loginError.textContent = data.message;
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            loginError.style.display = 'block';
                                            loginError.textContent = 'Đã xảy ra lỗi. Vui lòng thử lại.';
                                        });
                                });
                            }
                        });
                });
            });
        });
    });

 
</script>

<script>
    if (typeof toggleReaction !== 'function') {
        function toggleReaction(id) {
            // Lấy container dựa trên id
            const container = document.getElementById( id);
            // alert(container);
            // Kiểm tra và thay đổi trạng thái hiển thị
            const short = container.querySelector('.motion-short');
            const full = container.querySelector('.motion-full');
            // alert(full);
            if (full.style.display === "none") {
                // Hiện đầy đủ
                full.style.display = "block";
                // short.style.display = "none";
            } else {
                // Quay lại trạng thái rút gọn
                full.style.display = "none";
                // short.style.display = "block";
            }
        }
    }
    if (typeof show_motions !== 'function') {
        function show_motions(id) {
            // Lấy container dựa trên id
            const container = document.getElementById( id);
            const full = container.querySelector('.motion-full');
            // alert(full);
            full.style.display = "block";
        }
    } 
    
    if (typeof off_motions !== 'function') {
        function off_motions(id) {
            // Lấy container dựa trên id
            const container = document.getElementById( id);
            const full = container.querySelector('.motion-full');
            // alert(full);
            full.style.display = "none";
        }
    
    }
        // Ví dụ: Thêm sự kiện vào container
        document.querySelectorAll('.motion-container').forEach(function (container) {
            container.addEventListener('mouseenter', function () {
                // alert(this.id);
                show_motions(this.id); // Hiện reaction theo id
            });
    
            container.addEventListener('mouseleave', function () {
                off_motions(this.id); // Ẩn reaction theo id
            });
        });
    </script>
    <script>
    if (typeof toggleCommentBox !== 'function') {
        function toggleCommentBox($id) {
            // Tìm phần tử comment-box gần nút đã nhấn
            const commentBox = document.getElementById('comment-box-'+$id);
    
            // Kiểm tra trạng thái hiện tại
            if (commentBox.style.display === "none" || commentBox.style.display === "") {
                commentBox.style.display = "block"; // Hiển thị form comment
                button.textContent = "Ẩn comment"; // Đổi nội dung nút
            } else {
                commentBox.style.display = "none"; // Ẩn form comment
                button.textContent = "Add comment"; // Đổi nội dung nút
            }
        }
    
    }
    </script>
    <script>
        document.querySelectorAll('.btn-reaction').forEach(function(button) {
            button.addEventListener('click', function() {
                const reactionId = this.getAttribute('data-reaction-id');
                const postId = this.getAttribute('data-id');
                const item_code= this.getAttribute('item_code');
                const spinner = document.getElementById('spinner');
                spinner.style.display = 'block';
    
                fetch(`{{route("front.reacts.react")}}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ reaction_id: reactionId , item_id: postId,item_code:item_code})
                })
                .then(response => response.json())
                .then(data => {
                    spinner.style.display = 'none';
                    if (data.success) {
                        var summ = 0;
                        for (const key in data.reactions) {
                            summ += data.reactions[key];
                            if (data.reactions.hasOwnProperty(key)) {
                                var  span = document.getElementById('mcount-'+key+'-'+postId);
                                span.innerText =data.reactions[key];

                                
                            }
                        }
                        var span = document.getElementById('spmcount-'+postId+'-'+item_code);
                         span.innerText = summ;
                        // location.reload();
                    }
                    else
                    {
                        // alert(data.msg);
                        const loginPopup = document.getElementById('loginPopup');
                        loginPopup.style.display = 'flex';
                        const loginForm = document.getElementById('ajaxLoginForm');
                        loginForm.addEventListener('submit', function (e) {
                            e.preventDefault(); // Ngăn form gửi thông thường
                    
                            const formData = new FormData(loginForm);
                            fetch('{{ route('ajax.login') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: formData,
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        loginPopup.style.display = 'none';
                                        location.reload();
                                      
                                    } else {
                                        loginError.style.display = 'block';
                                        loginError.textContent = data.message;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    loginError.style.display = 'block';
                                    loginError.textContent = 'Đã xảy ra lỗi. Vui lòng thử lại.';
                                });
                        });
                    
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bookmarkButtons = document.querySelectorAll('.btn-bookmark');
            const spinner = document.getElementById('spinner');
            bookmarkButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const postId = this.getAttribute('data-post-id');
                    const item_code= this.getAttribute('item_code');
                    // Hiển thị spinner
                    spinner.style.display = 'block';
    
                    fetch(`{{route("front.bookmarks.bookmark")}}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ item_id: postId,item_code:item_code})
                    })
                    .then(response => response.json())
                    .then(data => {
                        spinner.style.display = 'none';
                        if(data.success==false)
                        {
                            const loginPopup = document.getElementById('loginPopup');
                            loginPopup.style.display = 'flex';
                            const loginForm = document.getElementById('ajaxLoginForm');
                            loginForm.addEventListener('submit', function (e) {
                                e.preventDefault(); // Ngăn form gửi thông thường
                        
                                const formData = new FormData(loginForm);
                                fetch('{{ route('ajax.login') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: formData,
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            loginPopup.style.display = 'none';
                                            location.reload();
                                        
                                        } else {
                                            loginError.style.display = 'block';
                                            loginError.textContent = data.message;
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        loginError.style.display = 'block';
                                        loginError.textContent = 'Đã xảy ra lỗi. Vui lòng thử lại.';
                                    });
                            });
                        }
                        if (data.status === 'added') {
                            this.classList.add('bookmarked');
                            // icon.classList.remove('icon-bookmark-outline');
                            // icon.classList.add('icon-bookmark');
                        } else if (data.status === 'removed') {
                            this.classList.remove('bookmarked');
                            // icon.classList.remove('icon-bookmark');
                            // icon.classList.add('icon-bookmark-outline');
                        }
                    });
                });
            });
        });
    </script>