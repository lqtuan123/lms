document.addEventListener("DOMContentLoaded", function () {
    // Bookmark functionality
    const bookmarkBtn = document.getElementById("bookmark-btn");

    const handleBookmarkClick = function (event) {
        event.preventDefault();

        let itemId = this.getAttribute("data-id");
        let itemCode = this.getAttribute("data-code");

        // Đổi trạng thái UI
        bookmarkBtn.classList.toggle("active");

        fetch(bookmarkUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                item_id: itemId,
                item_code: itemCode
            })
        })
            .then(response => response.json())
            .then(data => {
                // Ensure UI matches server state
                if (data.isBookmarked) {
                    bookmarkBtn.classList.add("active");
                } else {
                    bookmarkBtn.classList.remove("active");
                }
            })
            .catch(error => {
                console.error("Lỗi:", error);
            });
    };

    if (bookmarkBtn) {
        bookmarkBtn.addEventListener("click", handleBookmarkClick);
    }

    // Tab switching functionality
    const tabs = document.querySelectorAll('.book-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.getAttribute('data-tab');

            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            tab.classList.add('active');
            document.getElementById(`${tabId}-content`).classList.add('active');
        });
    });

    // Rating functionality
    const bookId = bookData.id;
    let currentPage = 1;
    let hasMoreRatings = true;
    let userRatingId = null;

    // Đảm bảo URL không có tham số thừa
    const baseUrl = siteUrl;

    // Định nghĩa biến xác định trạng thái đăng nhập
    const isLoggedIn = userLoggedIn;

    // Load user's existing rating if logged in
    if (isLoggedIn) {
        loadUserRating();
    }

    // Load initial ratings
    loadRatings();

    // Set up rating stars interaction
    const ratingStarsInput = document.getElementById('rating-stars-input');
    if (ratingStarsInput) {
        const stars = ratingStarsInput.querySelectorAll('.rating-star');

        stars.forEach((star, index) => {
            // Hover effect
            star.addEventListener('mouseover', () => {
                for (let i = 0; i <= index; i++) {
                    stars[i].textContent = '★';
                    stars[i].classList.add('filled');
                }
                for (let i = index + 1; i < stars.length; i++) {
                    stars[i].textContent = '☆';
                    stars[i].classList.remove('filled');
                }
            });

            // Click to select
            star.addEventListener('click', () => {
                // Đặt lại tất cả các sao
                stars.forEach(s => {
                    s.classList.remove('selected');
                });
                // Đánh dấu sao được chọn
                for (let i = 0; i <= index; i++) {
                    stars[i].classList.add('selected');
                }
                // Lưu giá trị rating
                document.getElementById('rating-value').value = index + 1;
            });
        });

        // Reset on mouseout if no selection
        ratingStarsInput.addEventListener('mouseout', () => {
            stars.forEach((star, index) => {
                if (!star.classList.contains('selected')) {
                    star.textContent = '☆';
                    star.classList.remove('filled');
                } else {
                    star.textContent = '★';
                    star.classList.add('filled');
                }
            });
        });
    }

    // Submit rating
    const submitRatingBtn = document.getElementById('submit-rating');
    if (submitRatingBtn) {
        submitRatingBtn.addEventListener('click', submitRating);
    }

    // Load more ratings button
    const loadMoreBtn = document.getElementById('load-more-ratings');
    if (loadMoreBtn) {
        loadMoreBtn.querySelector('button').addEventListener('click', () => {
            currentPage++;
            loadRatings(currentPage);
        });
    }

    // Functions
    function loadUserRating() {
        if (!isLoggedIn) {
            return; // Không làm gì nếu người dùng chưa đăng nhập
        }

        fetch(`${baseUrl}/ratings/user/${bookId}`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 404) {
                        return {
                            rating: null
                        };
                    }
                    throw new Error('Không thể tải đánh giá của bạn');
                }
                return response.json();
            })
            .then(data => {
                if (data.rating) {
                    const userRating = data.rating;
                    userRatingId = userRating.id;
                    const ratingValue = userRating.rating;
                    document.getElementById('rating-value').value = ratingValue;
                    document.getElementById('rating-comment').value = userRating.comment || '';

                    // Cập nhật sao đánh giá
                    const stars = document.querySelectorAll('.user-rating-star');
                    stars.forEach((star, index) => {
                        if (index < ratingValue) {
                            star.classList.add('filled');
                        } else {
                            star.classList.remove('filled');
                        }
                    });

                    // Cập nhật text nút submit
                    const submitRatingBtn = document.getElementById('submit-rating');
                    if (submitRatingBtn) {
                        submitRatingBtn.textContent = 'Cập nhật đánh giá';
                    }

                    // Hiển thị nút xóa
                    const deleteRatingBtn = document.getElementById('delete-rating');
                    if (deleteRatingBtn) {
                        deleteRatingBtn.classList.remove('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Lỗi khi tải đánh giá của người dùng:', error);
                // Hiển thị thông báo lỗi
                const errorElement = document.getElementById('rating-error');
                if (errorElement) {
                    errorElement.textContent = error.message;
                    errorElement.classList.remove('hidden');
                }
            });
    }

    function loadRatings(page = 1) {
        const baseUrl = window.location.origin;
        const loadingIndicator = document.getElementById('ratings-loading');
        const ratingsContainer = document.getElementById('ratings-container');

        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }

        fetch(`${baseUrl}/ratings/book/${bookId}?page=${page}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Không thể tải đánh giá');
                }
                return response.json();
            })
            .then(data => {
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }

                // Cập nhật thống kê đánh giá
                updateRatingStats(data.stats);

                // Cập nhật danh sách đánh giá
                if (ratingsContainer) {
                    ratingsContainer.innerHTML = '';

                    if (data.ratings.data && data.ratings.data.length > 0) {
                        data.ratings.data.forEach(rating => {
                            const ratingElement = createRatingElement(rating);
                            ratingsContainer.appendChild(ratingElement);
                        });

                        // Cập nhật phân trang
                        const paginationContainer = document.getElementById('ratings-pagination');
                        if (paginationContainer) {
                            paginationContainer.innerHTML = createPagination(data.ratings);
                        }
                    } else {
                        ratingsContainer.innerHTML =
                            '<p class="text-center text-gray-500 my-4">Chưa có đánh giá nào.</p>';
                    }
                }
            })
            .catch(error => {
                console.error('Lỗi khi tải đánh giá:', error);
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
                if (ratingsContainer) {
                    ratingsContainer.innerHTML =
                        '<p class="text-center text-red-500 my-4">Có lỗi xảy ra khi tải đánh giá. Vui lòng thử lại sau.</p>';
                }
            });
    }

    function submitRating() {
        const baseUrl = window.location.origin;
        const ratingValue = document.getElementById('rating-value').value;
        if (!ratingValue) {
            alert('Vui lòng chọn số sao cho đánh giá của bạn');
            return;
        }

        const comment = document.getElementById('rating-comment').value;

        // Disable submit button to prevent multiple submissions
        submitRatingBtn.disabled = true;
        submitRatingBtn.textContent = 'Đang gửi...';

        // Check if this is an update or a new rating
        const method = userRatingId ? 'PUT' : 'POST';
        const endpoint = userRatingId ?
            `${baseUrl}/ratings/book/${bookId}` :
            `${baseUrl}/ratings/book/${bookId}`;

        fetch(endpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                rating: ratingValue,
                comment: comment
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Cập nhật UI
                if (data.rating) {
                    // Lưu user rating ID để cập nhật sau này
                    userRatingId = data.rating.id;

                    // Hiển thị thông báo thành công
                    showToast(method === 'PUT' ? 'Đánh giá đã được cập nhật thành công!' :
                        'Gửi đánh giá thành công!');

                    // Cập nhật nút submit
                    submitRatingBtn.textContent = 'Cập nhật đánh giá';

                    // Hiển thị nút xóa đánh giá
                    const deleteRatingBtn = document.getElementById('delete-rating');
                    if (deleteRatingBtn) {
                        deleteRatingBtn.classList.remove('hidden');
                    }

                    // Tải lại đánh giá để cập nhật danh sách và stats
                    loadRatings();

                    // Tự động cuộn xuống phần đánh giá nếu là đánh giá mới
                    if (method === 'POST') {
                        const ratingsContainer = document.getElementById('ratings-container');
                        if (ratingsContainer) {
                            ratingsContainer.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    }
                } else if (data.errors) {
                    alert('Lỗi: ' + Object.values(data.errors).join('\n'));
                } else if (data.error) {
                    alert('Lỗi: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error submitting rating:', error);
                alert('Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại sau.');
            })
            .finally(() => {
                // Re-enable submit button
                submitRatingBtn.disabled = false;
                submitRatingBtn.textContent = userRatingId ? 'Cập nhật đánh giá' : 'Gửi đánh giá';
            });
    }

    function createRatingElement(rating) {
        const userId = currentUserId;
        const isCurrentUser = userId && rating.user_id === userId;

        const ratingElement = document.createElement('div');
        ratingElement.className = 'rating-item';
        ratingElement.setAttribute('data-rating-id', rating.id);

        // Default avatar if user has no avatar
        const defaultAvatar = `${baseUrl}/images/default-avatar.png`;
        const avatarUrl = rating.user && rating.user.photo ?
            rating.user.photo : defaultAvatar;

        ratingElement.innerHTML = `
                    <div class="rating-user-info">
                        <div class="rating-user-avatar">
                            <img src="${avatarUrl}" alt="User avatar">
                        </div>
                        <div class="rating-user-details">
                            <span class="rating-user-name">${rating.user ? rating.user.full_name : 'Người dùng ẩn danh'}</span>
                            <div class="rating-stars-display">
                                ${createStars(rating.rating)}
                            </div>
                            <span class="rating-date">${formatDate(rating.created_at)}</span>
                        </div>
                    </div>
                    ${rating.comment ? `<div class="rating-content">${escapeHtml(rating.comment)}</div>` : ''}
                    <div class="rating-actions">
                        <div class="rating-like">
                            <span class="rating-like-icon">👍</span>
                            <span class="rating-like-count">0</span>
                        </div>
                        ${isCurrentUser ? `
                                    <div class="ml-auto">
                                        <button class="delete-rating-btn text-red-600 hover:text-red-800 text-sm" data-id="${rating.id}">Xóa</button>
                                    </div>
                                ` : ''}
                    </div>
                `;

        // Add event listeners for buttons
        if (isCurrentUser) {
            setTimeout(() => {
                const deleteBtn = ratingElement.querySelector('.delete-rating-btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', () => {
                        if (confirm('Bạn có chắc chắn muốn xóa đánh giá này không?')) {
                            deleteRating(rating.id);
                        }
                    });
                }
            }, 0);
        }

        // Add like functionality
        setTimeout(() => {
            const likeBtn = ratingElement.querySelector('.rating-like');
            if (likeBtn) {
                likeBtn.addEventListener('click', () => {
                    const countEl = likeBtn.querySelector('.rating-like-count');
                    let count = parseInt(countEl.textContent);
                    if (!likeBtn.classList.contains('liked')) {
                        countEl.textContent = count + 1;
                        likeBtn.classList.add('liked');
                        likeBtn.style.color = 'var(--color-primary)';
                    } else {
                        countEl.textContent = count - 1;
                        likeBtn.classList.remove('liked');
                        likeBtn.style.color = 'var(--color-gray-600)';
                    }
                });
            }
        }, 0);

        return ratingElement;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function createStars(ratingValue) {
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(ratingValue)) {
                starsHtml += '<span class="rating-star filled">★</span>';
            } else if (i - 0.5 <= ratingValue) {
                starsHtml += '<span class="rating-star half">☆</span>';
            } else {
                starsHtml += '<span class="rating-star">☆</span>';
            }
        }
        return starsHtml;
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';

        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function updateRatingStats(stats) {
        // Update average rating display
        const avgDisplay = document.getElementById('rating-stats-average');
        const ratingAverageSm = document.getElementById('rating-average');
        const ratingCount = document.getElementById('rating-count');

        if (avgDisplay) avgDisplay.textContent = parseFloat(stats.average).toFixed(1);
        if (ratingAverageSm) ratingAverageSm.textContent = parseFloat(stats.average).toFixed(1);
        if (ratingCount) ratingCount.textContent = stats.count;

        // Update distribution bars
        for (let i = 5; i >= 1; i--) {
            const percentage = stats.count > 0 ? (stats.distribution[i] / stats.count) * 100 : 0;
            const fillElement = document.querySelector(`.rating-bar:nth-child(${6 - i}) .rating-bar-fill`);
            const countElement = document.querySelector(`.rating-bar:nth-child(${6 - i}) .rating-bar-count`);

            if (fillElement) fillElement.style.width = `${percentage}%`;
            if (countElement) countElement.textContent = stats.distribution[i];
        }

        // Update stars in main display
        const starsElement = document.getElementById('book-rating-display');
        if (starsElement) {
            starsElement.innerHTML = createStars(stats.average);
        }
    }

    function deleteRating(ratingId) {
        const baseUrl = window.location.origin;

        fetch(`${baseUrl}/ratings/${ratingId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Có lỗi xảy ra khi xóa đánh giá');
                    });
                }
                return response.json();
            })
            .then(data => {
                // Xóa đánh giá khỏi UI
                const ratingElement = document.querySelector(`.rating-item[data-rating-id="${ratingId}"]`);
                if (ratingElement) {
                    ratingElement.remove();
                }

                // Reset form nếu đây là đánh giá của người dùng hiện tại
                if (userRatingId === ratingId) {
                    userRatingId = null;

                    // Reset stars
                    const stars = document.querySelectorAll('#rating-stars-input .rating-star');
                    stars.forEach(star => {
                        star.textContent = '☆';
                        star.classList.remove('selected', 'filled');
                    });

                    // Reset comment
                    const commentElement = document.getElementById('rating-comment');
                    if (commentElement) {
                        commentElement.value = '';
                    }

                    // Reset rating value
                    document.getElementById('rating-value').value = '';

                    // Update submit button
                    if (submitRatingBtn) {
                        submitRatingBtn.textContent = 'Gửi đánh giá';
                    }

                    // Ẩn nút xóa
                    const deleteRatingBtn = document.getElementById('delete-rating');
                    if (deleteRatingBtn) {
                        deleteRatingBtn.classList.add('hidden');
                    }
                }

                // Tải lại đánh giá và thống kê
                loadRatings();

                // Hiển thị thông báo thành công
                showToast('Đánh giá đã được xóa thành công');
            })
            .catch(error => {
                console.error('Lỗi khi xóa đánh giá:', error);
                alert('Có lỗi xảy ra khi xóa đánh giá: ' + error.message);
            });
    }

    function createPagination(paginator) {
        if (!paginator || !paginator.last_page || paginator.last_page <= 1) {
            return '';
        }

        let html = '<div class="flex justify-center space-x-2 mt-4">';

        // Nút Prev
        if (paginator.current_page > 1) {
            html +=
                `<button class="pagination-btn bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded" data-page="${paginator.current_page - 1}">Trước</button>`;
        }

        // Các trang
        const totalPages = paginator.last_page;
        const currentPage = paginator.current_page;

        // Hiển thị tối đa 5 trang
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);

        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html +=
                    `<button class="pagination-btn bg-blue-600 text-white px-3 py-1 rounded" data-page="${i}">${i}</button>`;
            } else {
                html +=
                    `<button class="pagination-btn bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded" data-page="${i}">${i}</button>`;
            }
        }

        // Nút Next
        if (currentPage < totalPages) {
            html +=
                `<button class="pagination-btn bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded" data-page="${currentPage + 1}">Tiếp</button>`;
        }

        html += '</div>';

        // Thêm event listeners cho các nút phân trang
        setTimeout(() => {
            document.querySelectorAll('.pagination-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const page = parseInt(btn.getAttribute('data-page'));
                    loadRatings(page);
                });
            });
        }, 0);

        return html;
    }

    // Tải tài liệu sách nếu có
    if (hasResources) {
        loadBookResources();
    }
});
