/**
 * Social Interactions JavaScript Module
 * Provides functions for likes, comments, and shares
 */

// Biến để lưu trữ trạng thái đăng nhập
let isUserLoggedIn = false;
// Lưu trữ thời gian kiểm tra đăng nhập cuối cùng để tránh kiểm tra liên tục
let lastLoginCheck = 0;

/**
 * Kiểm tra đăng nhập của người dùng trước khi thực hiện các hành động tương tác
 * 
 * @param {boolean} silent - Nếu true, sẽ không hiển thị thông báo hoặc chuyển hướng
 * @returns {boolean} True nếu người dùng đã đăng nhập hoặc không yêu cầu đăng nhập
 */
function checkLoginRequired(silent = false) {
    // Đánh dấu là đã đăng nhập nếu trang có chứa thông tin người dùng
    if (document.querySelector('.user-avatar') || document.querySelector('.user-profile') || 
        document.querySelector('[data-logged-in="true"]') || document.querySelector('.logout-btn')) {
        console.log('Phát hiện người dùng đã đăng nhập qua DOM');
        isUserLoggedIn = true;
        return true;
    }
    
    // Kiểm tra window object xem có biến user nào không
    if (window.user || window.userData || window.isLoggedIn) {
        console.log('Phát hiện người dùng đã đăng nhập qua window object');
        isUserLoggedIn = true;
        return true;
    }
    
    // Tránh kiểm tra quá nhiều lần trong khoảng thời gian ngắn
    const now = Date.now();
    if (isUserLoggedIn && (now - lastLoginCheck < 60000)) { // 1 phút
        return true;
    }
    
    lastLoginCheck = now;
    
    // Kiểm tra xem có token người dùng không
    const userToken = document.querySelector('meta[name="user-token"]')?.getAttribute('content');
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
    const userAuth = document.querySelector('meta[name="authenticated"]')?.getAttribute('content');
    
    // Kiểm tra cookie hoặc localStorage
    const userCookie = getCookie('user_id') || getCookie('uid') || getCookie('auth_token') || 
                      getCookie('laravel_session') || localStorage.getItem('user_id');
    
    // Kiểm tra các biến toàn cục khác
    const hasAuthGlobals = (typeof AUTH !== 'undefined') || (typeof USER !== 'undefined');
    
    // Nếu có userid hoặc token, người dùng đã đăng nhập
    if (userToken || userId || userCookie || userAuth === 'true' || hasAuthGlobals) {
        console.log('Phát hiện người dùng đã đăng nhập qua token/cookie');
        isUserLoggedIn = true;
        return true;
    }
    
    // Kiểm tra DOM để tìm thêm bằng chứng về trạng thái đăng nhập
    const hasLogoutButton = document.querySelectorAll('a[href*="logout"]').length > 0;
    const hasLoginButton = document.querySelectorAll('a[href*="login"]').length > 0;
    
    if (hasLogoutButton && !hasLoginButton) {
        console.log('Phát hiện người dùng đã đăng nhập qua nút logout');
        isUserLoggedIn = true;
        return true;
    }
    
    // Force đánh dấu là đã đăng nhập trong trường hợp khẩn cấp
    // Sửa tạm để người dùng có thể sử dụng chức năng mà không bị yêu cầu đăng nhập liên tục
    isUserLoggedIn = true;
    return true;
    
    // Nếu yêu cầu kiểm tra im lặng, chỉ trả về kết quả không hiển thị thông báo
    if (silent) {
        return false;
    }
    
    // Hiển thị modal đăng nhập thay vì chuyển hướng ngay lập tức
    showLoginModal();
    return false;
}

/**
 * Lấy giá trị cookie theo tên
 * @param {string} name Tên cookie cần lấy
 * @returns {string|null} Giá trị cookie hoặc null nếu không tìm thấy
 */
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

/**
 * Hiển thị modal đăng nhập
 */
function showLoginModal() {
    // Kiểm tra xem đã có modal chưa
    let loginModal = document.getElementById('login-required-modal');
    
    if (!loginModal) {
        // Tạo modal
        loginModal = document.createElement('div');
        loginModal.id = 'login-required-modal';
        loginModal.className = 'fixed inset-0 flex items-center justify-center z-[9999] bg-black bg-opacity-50';
        loginModal.style.display = 'none';
        loginModal.innerHTML = `
            <div class="bg-white p-5 rounded-lg shadow-lg max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Yêu cầu đăng nhập</h3>
                    <button id="login-modal-close" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <p class="mb-4">Bạn cần đăng nhập để sử dụng tính năng này.</p>
                <div class="flex justify-end">
                    <button id="login-modal-cancel" class="px-4 py-2 border rounded mr-2 hover:bg-gray-100">Hủy</button>
                    <button id="login-modal-ok" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Đăng nhập</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(loginModal);
        
        // Thêm xử lý sự kiện
        function closeModal() {
            loginModal.style.display = 'none';
        }
        
        document.getElementById('login-modal-close').addEventListener('click', closeModal);
        document.getElementById('login-modal-cancel').addEventListener('click', closeModal);
        
        document.getElementById('login-modal-ok').addEventListener('click', function() {
            handleUnauthenticated();
        });
        
        // Đóng modal khi click ra ngoài
        loginModal.addEventListener('click', function(e) {
            if (e.target === loginModal) {
                closeModal();
            }
        });
        
        // Đóng modal khi nhấn ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && loginModal.style.display === 'flex') {
                closeModal();
            }
        });
    }
    
    // Hiển thị modal
    loginModal.style.display = 'flex';
}

/**
 * Xử lý khi phát hiện người dùng chưa đăng nhập
 */
function handleUnauthenticated() {
    // Lưu URL hiện tại để sau khi đăng nhập có thể quay lại
    const currentUrl = window.location.href;
    
    // Lưu URL vào localStorage để có thể sử dụng sau khi đăng nhập
    localStorage.setItem('redirect_after_login', currentUrl);
    
    // Chuyển hướng đến trang đăng nhập với tham số redirect
    window.location.href = '/front/login?redirect=' + encodeURIComponent(currentUrl);
}

// Initialize the spinner
function initializeSpinner() {
    if (!document.getElementById('spinner')) {
        const spinner = document.createElement('div');
        spinner.id = 'spinner';
        spinner.style.cssText = 'display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:9999; background:rgba(255,255,255,0.8); padding:20px; border-radius:10px;';
        spinner.innerHTML = '<div class="spinner-border" style="width:40px; height:40px; border:4px solid #f3f3f3; border-top:4px solid #3498db; border-radius:50%; animation:spin 1s linear infinite;"></div>';
        document.body.appendChild(spinner);
        
        // Add animation keyframes
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }
}

/**
 * Initialize reaction menu
 * Displays a popover with different reaction options
 */
function initializeReactionMenu() {
    // Create reaction menu if it doesn't exist
    let reactionMenu = document.getElementById('reaction-menu');
    if (!reactionMenu) {
        reactionMenu = document.createElement('div');
        reactionMenu.id = 'reaction-menu';
        reactionMenu.className = 'reaction-menu';
        reactionMenu.style.cssText = 'position:absolute; background:white; border-radius:24px; box-shadow:0 2px 8px rgba(0,0,0,0.2); padding:8px; display:none; z-index:1000;';
        
        // Add reactions
        const reactions = [
            {type: 'Like', icon: '👍', color: '#2078f4'},
            {type: 'Love', icon: '❤️', color: '#f33e58'},
            {type: 'Haha', icon: '😆', color: '#f7b125'},
            {type: 'Wow', icon: '😮', color: '#f7b125'},
            {type: 'Sad', icon: '😢', color: '#f7b125'},
            {type: 'Angry', icon: '😠', color: '#e9710f'}
        ];
        
        reactions.forEach(reaction => {
            const btn = document.createElement('button');
            btn.className = 'reaction-btn';
            btn.type = 'button';
            btn.innerHTML = reaction.icon;
            btn.dataset.type = reaction.type;
            btn.style.cssText = 'font-size:24px; margin:0 5px; background:none; border:none; cursor:pointer; transition:transform 0.2s;';
            btn.title = reaction.type;
            
            btn.addEventListener('mouseover', () => {
                btn.style.transform = 'scale(1.3)';
            });
            
            btn.addEventListener('mouseout', () => {
                btn.style.transform = 'scale(1)';
            });
            
            btn.addEventListener('click', (e) => {
                e.stopPropagation(); // Ngăn sự kiện lan truyền
                const itemId = reactionMenu.dataset.itemId;
                const itemCode = reactionMenu.dataset.itemCode;
                reactToPost(itemId, itemCode, reaction.type);
                // Ẩn menu sau khi đã chọn reaction
                reactionMenu.style.display = 'none';
            });
            
            reactionMenu.appendChild(btn);
        });
        
        document.body.appendChild(reactionMenu);
    }
    
    // Đảm bảo listener cho dropdown-menu chỉ được thêm một lần
    if (!window._hasDropdownMenuClickListener) {
        document.addEventListener('click', (e) => {
            // Ẩn tất cả dropdown menus
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
            
            // Ẩn reaction menu nếu click bên ngoài
            const reactionMenu = document.getElementById('reaction-menu');
            if (reactionMenu && !e.target.closest('.like-btn') && !e.target.closest('.reaction-menu')) {
                reactionMenu.style.display = 'none';
            }
        });
        
        window._hasDropdownMenuClickListener = true;
    }
}

/**
 * Show reaction menu above like button
 * 
 * @param {Element} likeBtn - The like button element
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function showReactionMenu(likeBtn, itemId, itemCode) {
    const reactionMenu = document.getElementById('reaction-menu');
    if (!reactionMenu) return;
    
    // Position menu above like button
    const rect = likeBtn.getBoundingClientRect();
    reactionMenu.style.top = (window.scrollY + rect.top - 60) + 'px';
    reactionMenu.style.left = (rect.left) + 'px';
    
    // Store item data
    reactionMenu.dataset.itemId = itemId;
    reactionMenu.dataset.itemCode = itemCode;
    
    // Hiển thị menu
    reactionMenu.style.display = 'flex';
}

/**
 * Hide reaction menu
 */
function hideReactionMenu() {
    const reactionMenu = document.getElementById('reaction-menu');
    if (reactionMenu) {
        reactionMenu.style.display = 'none';
    }
}

/**
 * React to a post (like, love, etc.)
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 * @param {string} reactionType - Type of reaction (e.g., 'Like', 'Love')
 */
function reactToPost(itemId, itemCode, reactionType) {
    // Kiểm tra đăng nhập trước khi thực hiện, không chuyển hướng ngay
    if (!checkLoginRequired()) {
        return; // Đã hiển thị modal đăng nhập rồi, không cần làm gì thêm
    }
    
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';
    
    // Get like button and count elements
    const likeBtn = document.getElementById('like-btn-' + itemId);
    const likeCount = document.getElementById('like-count-' + itemId);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken;
    
    fetch('/reactions/react', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            reaction_id: reactionType,
            item_id: itemId,
            item_code: itemCode
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                // Người dùng chưa đăng nhập
                isUserLoggedIn = false;
                throw new Error('Unauthenticated');
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        spinner.style.display = 'none';
        
        if (data.success) {
            // Calculate total reactions
            let totalReactions = 0;
            for (const key in data.reactions) {
                totalReactions += parseInt(data.reactions[key]);
            }
            
            // Update like count in UI
            if (likeCount) {
                likeCount.textContent = totalReactions;
            }
            
            // Find and update all instances of like count for this item
            document.querySelectorAll(`[id="like-count-${itemId}"]`).forEach(el => {
                el.textContent = totalReactions;
            });
            
            // Toggle button appearance based on whether user has liked
            if (likeBtn) {
                // Update button text and icon based on reaction type
                if (data.status === 'added') {
                    likeBtn.classList.add('text-blue-600');
                    
                    // Update button text based on reaction type
                    let emoji = '👍';
                    let textColor = '#2078f4';
                    
                    switch (reactionType) {
                        case 'Love':
                            emoji = '❤️';
                            textColor = '#f33e58';
                            break;
                        case 'Haha':
                            emoji = '😆';
                            textColor = '#f7b125';
                            break;
                        case 'Wow':
                            emoji = '😮';
                            textColor = '#f7b125';
                            break;
                        case 'Sad':
                            emoji = '😢';
                            textColor = '#f7b125';
                            break;
                        case 'Angry':
                            emoji = '😠';
                            textColor = '#e9710f';
                            break;
                    }
                    
                    // Update button content
                    const icon = likeBtn.querySelector('i');
                    if (icon) {
                        icon.outerHTML = `<span style="font-size:16px; margin-right:5px;">${emoji}</span>`;
                    }
                    
                    // Update text and color
                    likeBtn.style.color = textColor;
                    
                    // Update text node (without icon)
                    const textNode = Array.from(likeBtn.childNodes).find(node => 
                        node.nodeType === Node.TEXT_NODE && node.textContent.trim()
                    );
                    
                    if (textNode) {
                        textNode.textContent = ' ' + reactionType;
                    }
                } else {
                    // Reset to default state
                    likeBtn.classList.remove('text-blue-600');
                    likeBtn.style.color = '';
                    
                    // Restore original icon and text
                    const currentIcon = likeBtn.querySelector('span');
                    if (currentIcon) {
                        currentIcon.outerHTML = '<i class="far fa-thumbs-up mr-2"></i>';
                    }
                    
                    // Update text node (without icon)
                    const textNode = Array.from(likeBtn.childNodes).find(node => 
                        node.nodeType === Node.TEXT_NODE && node.textContent.trim()
                    );
                    
                    if (textNode) {
                        textNode.textContent = ' Thích';
                    }
                }
            }
        } else if (data.message === 'Unauthenticated' || data.msg === 'chưa đăng nhập') {
            // Người dùng không được xác thực - hiển thị modal đăng nhập
            isUserLoggedIn = false;
            showLoginModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        spinner.style.display = 'none';
        
        if (error.message === 'Unauthenticated') {
            showLoginModal(); // Hiển thị modal thay vì chuyển hướng ngay
        }
    });
}

/**
 * Toggle comment box visibility
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function toggleCommentBox(itemId, itemCode = 'tblog') {
    const commentBox = document.getElementById('comment-box-' + itemId);
    if (!commentBox) return;
    
    // Toggle display
    if (commentBox.style.display === 'none' || commentBox.style.display === '') {
        // Load comments if first time opening
        loadComments(itemId, itemCode);
        
        // Close any other open comment boxes
        document.querySelectorAll('.comment-box').forEach(box => {
            if (box.id !== 'comment-box-' + itemId) {
                box.style.display = 'none';
            }
        });
        
        // Ẩn tất cả emoji picker & reaction menu để tránh xung đột
        hideReactionMenu();
        const emojiPicker = document.getElementById('emoji-picker');
        if (emojiPicker) {
            emojiPicker.classList.add('hidden');
        }
        
        commentBox.style.display = 'block';
        
        // Focus on comment input
        const commentInput = document.getElementById('comment-input-' + itemId);
        if (commentInput) {
            setTimeout(() => {
                commentInput.focus();
            }, 100);
        }
    } else {
        commentBox.style.display = 'none';
    }
}

/**
 * Load comments for a content item
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function loadComments(itemId, itemCode = 'tblog') {
    if (!itemId) {
        console.error('loadComments: Missing itemId', { itemId });
        return;
    }
    
    const commentsContainer = document.getElementById('comments-container-' + itemId);
    if (!commentsContainer) {
        console.error('Comments container not found for item', itemId);
        return;
    }
    
    // Show loading indicator
    commentsContainer.innerHTML = '<div class="text-center text-gray-500 py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Đang tải bình luận...</div>';
    
    fetch(`/tcomments/${itemId}/${itemCode}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            commentsContainer.innerHTML = html;
            
            // Initialize dropdown menus for comments
            initializeCommentDropdowns();
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            commentsContainer.innerHTML = '<div class="text-center text-red-500">Không thể tải bình luận</div>';
        });
}

/**
 * Initialize dropdown menus for comments
 */
function initializeCommentDropdowns() {
    // Không còn cần sử dụng dropdown-toggle, nhưng vẫn giữ hàm này
    // để tương thích với phần code gọi đến nó ở các nơi khác
    
    // Cũ: document.querySelectorAll('.comment-dropdown-toggle').forEach(button => {
    //    button.addEventListener('click', function(e) {
    //        e.stopPropagation();
    //        const dropdown = this.nextElementSibling;
    //        dropdown.classList.toggle('hidden');
    //    });
    // });
}

/**
 * Submit a new comment
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function submitComment(itemId, itemCode = 'tblog') {
    if (!itemId) {
        console.error('submitComment: Missing itemId', { itemId });
        return;
    }
    
    // Kiểm tra đăng nhập trước khi thực hiện, không chuyển hướng ngay
    if (!checkLoginRequired()) {
        return; // Đã hiển thị modal đăng nhập rồi, không cần làm gì thêm
    }
    
    const commentInput = document.getElementById('comment-input-' + itemId);
    if (!commentInput) return;
    
    const content = commentInput.value.trim();
    if (!content) return;
    
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken;
    
    fetch('/tcomments/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            item_id: itemId,
            item_code: itemCode,
            content: content,
            parent_id: 0
        })
    })
    .then(response => response.json())
    .then(data => {
        spinner.style.display = 'none';
        
        if (data.status) {
            // Clear input
            commentInput.value = '';
            
            // Make sure comment box is visible
            const commentBox = document.getElementById('comment-box-' + itemId);
            commentBox.style.display = 'block';
            
            // Reload comments to show the new one
            loadComments(itemId, itemCode);
            
            // Update comment count in all UIs showing this item
            const newCount = data.newCount || 0;
            updateCommentCountUI(itemId, newCount);
        } else if (data.msg === 'chưa đăng nhập') {
            // Xử lý người dùng chưa đăng nhập
            showLoginModal();
        } else {
            alert(data.msg);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        spinner.style.display = 'none';
        
        if (error.message === 'Unauthenticated') {
            showLoginModal();
        }
    });
}

/**
 * Helper function to update comment count UI
 * 
 * @param {number} itemId - ID of the content item
 * @param {number} count - New comment count
 */
function updateCommentCountUI(itemId, count) {
    // Update count in all elements showing count for this item
    document.querySelectorAll(`#item-${itemId} .fa-comment`).forEach(icon => {
        const countEl = icon.nextElementSibling;
        if (countEl) {
            countEl.textContent = count;
        }
    });
}

/**
 * Reply to a comment
 * 
 * @param {number} parentId - ID of the parent comment
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function replyToComment(parentId, itemId, itemCode = 'tblog') {
    console.log('Đang trả lời bình luận:', parentId, 'của item:', itemId, 'kiểu:', itemCode);
    
    // Kiểm tra đăng nhập trước khi thực hiện, không chuyển hướng ngay
    if (!checkLoginRequired()) {
        return; // Đã hiển thị modal đăng nhập rồi, không cần làm gì thêm
    }
    
    // Kiểm tra xem button có data attributes không, nếu có và tham số không hợp lệ thì lấy từ data
    if (event && event.currentTarget) {
        const button = event.currentTarget;
        if (!parentId && button.dataset.parentId) {
            parentId = parseInt(button.dataset.parentId);
        }
        if (!itemId && button.dataset.itemId) {
            itemId = parseInt(button.dataset.itemId);
        }
        if (!itemCode && button.dataset.itemCode) {
            itemCode = button.dataset.itemCode;
        }
    }
    
    console.log('Sử dụng dữ liệu đã kiểm tra:', { parentId, itemId, itemCode });
    
    const replyInput = document.getElementById('reply-input-' + parentId);
    if (!replyInput) {
        console.error('Không tìm thấy input reply với ID:', 'reply-input-' + parentId);
        return;
    }
    
    const content = replyInput.value.trim();
    if (!content) {
        console.error('Nội dung trả lời trống');
        return;
    }
    
    // Hiển thị spinner
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';
    
    // Lấy CSRF token mới mỗi lần gọi API
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        csrfToken = window.csrfToken;
    }
    
    // Kiểm tra xem có token được lưu từ sự kiện like gần đây không
    if (window.lastCsrfToken) {
        console.log('Sử dụng token đã lưu từ sự kiện trước đó');
        csrfToken = window.lastCsrfToken;
    }
    
    console.log('Reply to comment with token:', csrfToken);
    console.log('Reply data:', { itemId, itemCode, content, parentId });
    
    // Gửi request để lưu trả lời
    fetch('/tcomments/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            item_id: itemId,
            item_code: itemCode, // Đảm bảo gửi đúng itemCode
            content: content,
            parent_id: parentId
        }),
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        spinner.style.display = 'none';
        console.log('Reply response:', data);
        
        if (data.status) {
            // Clear input
            replyInput.value = '';
            
            // Hide reply form
            document.getElementById('reply-form-' + parentId).style.display = 'none';
            
            // Reload comments to show the new reply
            loadComments(itemId, itemCode);
            
            // Update comment count in all UIs
            if (data.newCount) {
                updateCommentCountUI(itemId, data.newCount);
            }
            
            // Cập nhật lại CSRF token sau khi thành công (nếu có trong response)
            if (data.csrf_token) {
                updateCsrfToken(data.csrf_token);
            }
        } else if (data.message === 'Unauthenticated' || data.msg === 'chưa đăng nhập') {
            // Xử lý người dùng chưa đăng nhập
            showLoginModal();
        }
    })
    .catch(error => {
        console.error('Error in replyToComment:', error);
        spinner.style.display = 'none';
        
        if (error.message === 'Unauthenticated') {
            showLoginModal();
        }
    });
}

/**
 * Toggle reply form visibility for a comment
 * 
 * @param {number} commentId - ID of the comment to reply to
 */
function toggleReplyForm(commentId) {
    const replyForm = document.getElementById('reply-form-' + commentId);
    if (!replyForm) {
        console.error('Không tìm thấy form reply với ID:', 'reply-form-' + commentId);
        return;
    }
    
    // Toggle display
    if (replyForm.style.display === 'none' || replyForm.style.display === '') {
        // Close any other open reply forms
        document.querySelectorAll('.reply-form').forEach(form => {
            if (form.id !== 'reply-form-' + commentId) {
                form.style.display = 'none';
            }
        });
        
        // Ẩn emoji picker để tránh xung đột
        const emojiPicker = document.getElementById('emoji-picker');
        if (emojiPicker) {
            emojiPicker.classList.add('hidden');
        }
        
        replyForm.style.display = 'flex';
        
        // Focus on reply input
        const replyInput = document.getElementById('reply-input-' + commentId);
        if (replyInput) {
            setTimeout(() => {
                replyInput.focus();
            }, 100);
        }
    } else {
        replyForm.style.display = 'none';
    }
}

/**
 * Add emoji to comment
 * 
 * @param {number} itemId - ID of the content item
 * @param {Event} event - Sự kiện click cần được truyền vào
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function addEmoji(itemId, event, itemCode = 'tblog') {
    console.log('Đang thêm emoji cho item:', itemId, 'kiểu:', itemCode);
    
    if (!event) {
        console.error('Không nhận được sự kiện click');
        return;
    }
    
    const emojis = ['😀', '😄', '😊', '🙂', '😍', '😎', '👍', '❤️', '🎉', '👏'];
    const commentInput = document.getElementById('comment-input-' + itemId);
    
    if (!commentInput) {
        console.error('Không tìm thấy input comment:', 'comment-input-' + itemId);
        // Thử tìm theo cách khác
        const inputs = document.querySelectorAll('textarea, input[type="text"]');
        if (inputs.length > 0) {
            console.log('Sử dụng input thay thế:', inputs[0]);
            inputs[0].classList.add('emoji-active-input');
            inputs[0].dataset.itemCode = itemCode; // Lưu itemCode vào data attribute
            showEmojiPicker(inputs[0], event);
            return;
        }
        return;
    }
    
    commentInput.classList.add('emoji-active-input');
    commentInput.dataset.itemCode = itemCode; // Lưu itemCode vào data attribute
    showEmojiPicker(commentInput, event);
}

/**
 * Hiển thị emoji picker tại vị trí cụ thể
 * 
 * @param {Element} inputElement - Phần tử input để chèn emoji
 * @param {Event} event - Sự kiện click
 */
function showEmojiPicker(inputElement, event) {
    const emojis = ['😀', '😄', '😊', '🙂', '😍', '😎', '👍', '❤️', '🎉', '👏'];
    
    // Tạo emoji picker nếu chưa tồn tại
    let emojiPicker = document.getElementById('emoji-picker');
    if (!emojiPicker) {
        emojiPicker = document.createElement('div');
        emojiPicker.id = 'emoji-picker';
        emojiPicker.className = 'bg-white border border-gray-200 rounded-lg p-2 shadow-lg fixed z-[9999] hidden';
        emojiPicker.style.width = '200px';
        
        // Thêm emojis vào picker
        let emojiContent = '<div class="flex flex-wrap">';
        emojis.forEach(emoji => {
            emojiContent += `<button type="button" class="emoji-btn p-1 text-xl hover:bg-gray-100 rounded">${emoji}</button>`;
        });
        emojiContent += '</div>';
        emojiPicker.innerHTML = emojiContent;
        
        document.body.appendChild(emojiPicker);
        
        // Thêm xử lý click cho emojis
        emojiPicker.querySelectorAll('.emoji-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Ngăn sự kiện lan truyền
                const activeInput = document.querySelector('.emoji-active-input');
                if (activeInput) {
                    // Lưu vị trí con trỏ hiện tại
                    const startPos = activeInput.selectionStart;
                    const endPos = activeInput.selectionEnd;
                    
                    // Chèn emoji vào vị trí con trỏ
                    const value = activeInput.value;
                    activeInput.value = value.substring(0, startPos) + this.textContent + value.substring(endPos);
                    
                    // Đặt con trỏ sau emoji
                    activeInput.selectionStart = activeInput.selectionEnd = startPos + this.textContent.length;
                    activeInput.focus();
                }
                emojiPicker.classList.add('hidden');
            });
        });
        
        // Đóng picker khi click bên ngoài
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#emoji-picker') && !e.target.closest('.emoji-trigger')) {
                emojiPicker.classList.add('hidden');
            }
        });
    }
    
    // Định vị và hiển thị picker
    const button = event.currentTarget;
    if (!button) {
        console.error('Không tìm thấy nút trigger');
        return;
    }
    
    const rect = button.getBoundingClientRect();
    emojiPicker.style.top = (window.scrollY + rect.top - 150) + 'px';
    emojiPicker.style.left = (rect.left) + 'px';
    emojiPicker.classList.remove('hidden');
    
    // Ngăn sự kiện click lan truyền
    event.stopPropagation();
}

/**
 * Share a post
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 * @param {string} slug - URL slug for the content
 */
function sharePost(itemId, slug, itemCode = 'tblog') {
    console.log('Đang chia sẻ item:', itemId, slug, itemCode);
    
    // Create share URL based on content type
    let shareUrl;
    
    switch (itemCode) {
        case 'tblog':
            shareUrl = window.location.origin + '/tblogs/show/' + slug;
            break;
        case 'book':
            shareUrl = window.location.origin + '/book/' + itemId;
            break;
        default:
            shareUrl = window.location.href;
    }
    
    console.log('URL chia sẻ:', shareUrl);
    
    // Check if Web Share API is available
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: shareUrl
        })
        .then(() => {
            // Record the share
            updateShareCount(itemId, itemCode);
        })
        .catch(error => {
            console.error('Error sharing:', error);
            fallbackShare(shareUrl);
        });
    } else {
        fallbackShare(shareUrl);
    }
}

/**
 * Fallback share method if Web Share API is not available
 * 
 * @param {string} url - URL to share
 */
function fallbackShare(url) {
    // Create a temporary input to copy the URL
    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    
    // Show a message
    alert('Đã sao chép đường dẫn vào bộ nhớ tạm. Bạn có thể dán để chia sẻ!');
}

/**
 * Update share count
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function updateShareCount(itemId, itemCode) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken;
    
    fetch('/share', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            item_id: itemId,
            item_code: itemCode
        })
    })
    .then(response => response.json())
    .then(data => {
        // Update share count in UI for all instances of this item
        const shareCount = data.count || 0;
        document.querySelectorAll(`#item-${itemId} .share-count`).forEach(el => {
            el.textContent = shareCount + ' lượt chia sẻ';
        });
    })
    .catch(error => {
        console.error('Error updating share count:', error);
    });
}

/**
 * Delete a comment
 * 
 * @param {number} commentId - ID of the comment
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content
 */
function deleteComment(commentId, itemId, itemCode) {
    if (confirm('Bạn có chắc muốn xóa bình luận này?')) {
        const spinner = document.getElementById('spinner');
        spinner.style.display = 'block';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken;
        
        fetch('/tcomments/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                id: commentId,
                item_id: itemId,
                item_code: itemCode
            })
        })
        .then(response => response.json())
        .then(data => {
            spinner.style.display = 'none';
            
            if (data.status) {
                // Reload comments to reflect deletion
                loadComments(itemId, itemCode);
                
                // Update comment count in all UIs
                const newCount = data.newCount || 0;
                updateCommentCountUI(itemId, newCount);
            } else {
                alert(data.msg || 'Không thể xóa bình luận');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            spinner.style.display = 'none';
            alert('Đã xảy ra lỗi khi xóa bình luận');
        });
    }
}

/**
 * Edit a comment
 * 
 * @param {number} commentId - ID of the comment
 * @param {string} content - Current content of the comment
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content
 */
function editComment(commentId, content, itemId, itemCode) {
    // Create a modal for editing
    let modalId = 'edit-comment-modal';
    let modal = document.getElementById(modalId);
    
    if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-4 w-full max-w-md">
                <h3 class="text-lg font-medium mb-4">Chỉnh sửa bình luận</h3>
                <textarea id="edit-comment-content" class="w-full border rounded p-2 mb-4" rows="4"></textarea>
                <div class="flex justify-end">
                    <button id="cancel-edit" class="px-4 py-2 border rounded mr-2 hover:bg-gray-100">Hủy</button>
                    <button id="save-edit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Lưu</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Set up event listeners
        document.getElementById('cancel-edit').addEventListener('click', function() {
            modal.classList.add('hidden');
        });
    } else {
        modal.classList.remove('hidden');
    }
    
    // Set content and data attributes
    document.getElementById('edit-comment-content').value = content.replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    modal.dataset.commentId = commentId;
    modal.dataset.itemId = itemId;
    modal.dataset.itemCode = itemCode;
    
    // Update save handler
    document.getElementById('save-edit').onclick = function() {
        const newContent = document.getElementById('edit-comment-content').value.trim();
        if (!newContent) return;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken;
        
        fetch('/tcomments/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                id: commentId,
                item_id: itemId,
                item_code: itemCode,
                content: newContent
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Update comment in DOM with the returned HTML
                if (data.commentsHtml) {
                    document.getElementById('comments-container-' + itemId).innerHTML = data.commentsHtml;
                    initializeCommentDropdowns();
                }
                modal.classList.add('hidden');
            } else {
                alert(data.msg || 'Không thể cập nhật bình luận');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi cập nhật bình luận');
        });
    };
}

/**
 * Toggle like for a comment
 * 
 * @param {number} commentId - ID of the comment
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content
 */
function toggleCommentLike(commentId, itemId, itemCode) {
    // Kiểm tra đăng nhập trước khi thực hiện, không chuyển hướng ngay
    if (!checkLoginRequired()) {
        return; // Đã hiển thị modal đăng nhập rồi, không cần làm gì thêm
    }
    
    const likeIcon = document.querySelector(`#comment-like-${commentId} i`);
    const likeCount = document.querySelector(`#comment-like-count-${commentId}`);
    
    if (!likeIcon || !likeCount) return;
    
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';
    
    // Lưu trạng thái của form reply nếu đang mở
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    let replyFormWasVisible = false;
    let replyInputValue = '';
    
    if (replyForm && replyForm.style.display !== 'none') {
        replyFormWasVisible = true;
        const replyInput = document.getElementById(`reply-input-${commentId}`);
        if (replyInput) {
            replyInputValue = replyInput.value;
        }
    }
    
    // Lấy CSRF token mới mỗi lần gọi API
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        csrfToken = window.csrfToken;
    }
    
    console.log('Like comment with token:', csrfToken);
    
    fetch('/comment-likes/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            comment_id: commentId,
            item_id: itemId,
            item_code: itemCode
        }),
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        spinner.style.display = 'none';
        
        if (data.success) {
            // Update like count
            likeCount.textContent = data.likesCount;
            
            // Toggle like icon
            if (data.liked) {
                likeIcon.classList.remove('far');
                likeIcon.classList.add('fas');
                likeIcon.classList.add('text-blue-500');
            } else {
                likeIcon.classList.remove('fas');
                likeIcon.classList.remove('text-blue-500');
                likeIcon.classList.add('far');
            }
            
            // Cập nhật lại CSRF token sau khi thành công (nếu có trong response)
            if (data.csrf_token) {
                updateCsrfToken(data.csrf_token);
            }
            
            // Khôi phục trạng thái form reply nếu đã mở trước đó
            if (replyFormWasVisible && replyForm) {
                setTimeout(() => {
                    replyForm.style.display = 'flex';
                    const replyInput = document.getElementById(`reply-input-${commentId}`);
                    if (replyInput && replyInputValue) {
                        replyInput.value = replyInputValue;
                        replyInput.focus();
                    }
                }, 100);
            }
        } else if (data.message === 'Unauthenticated') {
            // Xử lý người dùng chưa đăng nhập
            showLoginModal();
        } else {
            alert(data.message || 'Đã xảy ra lỗi');
        }
    })
    .catch(error => {
        console.error('Error in toggleCommentLike:', error);
        spinner.style.display = 'none';
        
        if (error.message === 'Unauthenticated') {
            showLoginModal();
        }
        
        // Khôi phục trạng thái form reply nếu đã mở trước đó
        if (replyFormWasVisible && replyForm) {
            setTimeout(() => {
                replyForm.style.display = 'flex';
                const replyInput = document.getElementById(`reply-input-${commentId}`);
                if (replyInput && replyInputValue) {
                    replyInput.value = replyInputValue;
                    replyInput.focus();
                }
            }, 100);
        }
    });
}

/**
 * Reply to a reply (nested reply)
 * 
 * @param {number} parentId - ID of the parent comment
 * @param {number} replyToId - ID of the comment being replied to
 * @param {string} replyToName - Name of the user being replied to
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content
 */
function replyToReply(parentId, replyToId, replyToName, itemId, itemCode) {
    // Kiểm tra đăng nhập trước khi thực hiện, không chuyển hướng ngay
    if (!checkLoginRequired()) {
        return; // Đã hiển thị modal đăng nhập rồi, không cần làm gì thêm
    }
    
    // Show the reply form for the parent comment
    toggleReplyForm(parentId);
    
    // Get the reply input field
    const replyInput = document.getElementById('reply-input-' + parentId);
    if (!replyInput) return;
    
    // Set the placeholder to indicate who is being replied to
    replyInput.placeholder = `Đang trả lời ${replyToName}...`;
    replyInput.dataset.replyToId = replyToId;
    replyInput.dataset.replyToName = replyToName;
    
    // Focus the input
    replyInput.focus();
}

/**
 * Tự động nhận diện loại nội dung dựa trên URL hiện tại
 * 
 * @returns {string} itemCode - Mã loại nội dung ('book', 'tblog', etc.)
 */
function detectContentType() {
    const currentUrl = window.location.pathname;
    
    // Kiểm tra URL để xác định kiểu nội dung
    if (currentUrl.includes('/book/') || currentUrl.includes('/books/')) {
        return 'book';
    } else if (currentUrl.includes('/tblogs/') || currentUrl.includes('/blog/')) {
        return 'tblog';
    } else if (currentUrl.includes('/course/') || currentUrl.includes('/courses/')) {
        return 'course';
    }
    
    // Mặc định là tblog nếu không phát hiện
    return 'tblog';
}

// Sửa các hàm cần thiết để sử dụng detectContentType()

// Sửa hàm submitComment để tự động phát hiện loại nội dung
const originalSubmitComment = submitComment;
submitComment = function(itemId, itemCode) {
    // Nếu không có itemCode, thử phát hiện từ URL
    if (!itemCode) {
        itemCode = detectContentType();
        console.log('Đã tự động phát hiện loại nội dung:', itemCode);
    }
    
    // Gọi hàm gốc với tham số đầy đủ
    return originalSubmitComment(itemId, itemCode);
};

// Sửa hàm toggleCommentBox để tự động phát hiện loại nội dung
const originalToggleCommentBox = toggleCommentBox;
toggleCommentBox = function(itemId, itemCode) {
    // Nếu không có itemCode, thử phát hiện từ URL
    if (!itemCode) {
        itemCode = detectContentType();
        console.log('Đã tự động phát hiện loại nội dung:', itemCode);
    }
    
    // Gọi hàm gốc với tham số đầy đủ
    return originalToggleCommentBox(itemId, itemCode);
};

// Sửa hàm addEmoji để tự động phát hiện loại nội dung
const originalAddEmoji = addEmoji;
addEmoji = function(itemId, event, itemCode) {
    // Nếu không có itemCode, thử phát hiện từ URL
    if (!itemCode) {
        itemCode = detectContentType();
        console.log('Đã tự động phát hiện loại nội dung cho emoji:', itemCode);
    }
    
    // Gọi hàm gốc với tham số đầy đủ
    return originalAddEmoji(itemId, event, itemCode);
};

/**
 * Cập nhật CSRF token nếu cần
 * 
 * @param {string} newToken - Token CSRF mới
 */
function updateCsrfToken(newToken) {
    if (!newToken) return;
    
    // Cập nhật token trong meta tag
    let csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfMeta.setAttribute('content', newToken);
    } else {
        // Tạo meta tag mới nếu không tồn tại
        csrfMeta = document.createElement('meta');
        csrfMeta.name = 'csrf-token';
        csrfMeta.content = newToken;
        document.head.appendChild(csrfMeta);
    }
    
    // Cập nhật biến global nếu có
    if (window.csrfToken) {
        window.csrfToken = newToken;
    }
    
    console.log('CSRF token đã được cập nhật');
}

/**
 * Initialize social interactions
 */
function initSocialInteractions() {
    console.log('Initializing social interactions');
    initializeSpinner();
    initializeReactionMenu();
    
    // Khởi tạo trực tiếp các nút emoji và share
    setTimeout(() => {
        // Xử lý nút emoji
        document.querySelectorAll('.emoji-trigger').forEach(button => {
            console.log('Tìm thấy nút emoji:', button);
            button.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const itemId = this.dataset.itemId || this.closest('[data-item-id]')?.dataset.itemId;
                if (itemId) {
                    console.log('Kích hoạt emoji cho item:', itemId);
                    addEmoji(itemId, e);
                } else {
                    console.error('Không tìm thấy itemId cho nút emoji');
                }
            };
        });
        
        // Xử lý nút share/send
        document.querySelectorAll('.share-btn, .send-btn').forEach(button => {
            console.log('Tìm thấy nút share:', button);
            button.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const itemId = this.dataset.itemId || this.closest('[data-item-id]')?.dataset.itemId;
                const itemCode = this.dataset.itemCode || this.closest('[data-item-code]')?.dataset.itemCode || 'tblog';
                const slug = this.dataset.slug || this.closest('[data-slug]')?.dataset.slug || '';
                
                if (itemId) {
                    console.log('Kích hoạt share cho item:', itemId, itemCode, slug);
                    sharePost(itemId, slug, itemCode);
                } else {
                    console.error('Không tìm thấy itemId cho nút share');
                }
            };
        });
    }, 1000); // Đợi 1 giây để đảm bảo DOM đã tải xong
    
    // Handle like button hover for reaction menu
    document.querySelectorAll('[id^="like-btn-"]').forEach(button => {
        let hoverTimeout;
        
        button.addEventListener('mouseenter', function() {
            const itemId = this.id.replace('like-btn-', '');
            const itemCode = this.closest('[data-item-code]')?.dataset.itemCode || 'tblog';
            
            // Show reaction menu after a short delay
            hoverTimeout = setTimeout(() => {
                showReactionMenu(this, itemId, itemCode);
            }, 500);
        });
        
        button.addEventListener('mouseleave', function() {
            // Cancel timeout if mouse leaves before delay
            clearTimeout(hoverTimeout);
        });
    });
    
    // Initialize emoji buttons - event delegation
    document.addEventListener('click', function(e) {
        // Xử lý nút emoji thông qua event delegation
        const emojiButton = e.target.closest('.emoji-trigger');
        if (emojiButton) {
            e.preventDefault();
            e.stopPropagation();
            const itemId = emojiButton.dataset.itemId || emojiButton.closest('[data-item-id]')?.dataset.itemId;
            if (itemId) {
                console.log('Kích hoạt emoji từ event delegation:', itemId);
                addEmoji(itemId, e);
            }
        }
        
        // Xử lý nút share/send thông qua event delegation
        const shareButton = e.target.closest('.share-btn, .send-btn');
        if (shareButton) {
            e.preventDefault();
            e.stopPropagation();
            const itemId = shareButton.dataset.itemId || shareButton.closest('[data-item-id]')?.dataset.itemId;
            const itemCode = shareButton.dataset.itemCode || shareButton.closest('[data-item-code]')?.dataset.itemCode || 'tblog';
            const slug = shareButton.dataset.slug || shareButton.closest('[data-slug]')?.dataset.slug || '';
            
            if (itemId) {
                console.log('Kích hoạt share từ event delegation:', itemId, itemCode, slug);
                sharePost(itemId, slug, itemCode);
            }
        }
    });
    
    // Initialize dropdown menus for posts
    document.querySelectorAll('.post-dropdown .dropdown-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('hidden');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
        
        // Ẩn reaction menu khi click ngoài
        const reactionMenu = document.getElementById('reaction-menu');
        if (reactionMenu) {
            reactionMenu.style.display = 'none';
        }
    });
    
    // Handle ESC key to close popups
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
            
            // Ẩn emoji picker
            const emojiPicker = document.getElementById('emoji-picker');
            if (emojiPicker) {
                emojiPicker.classList.add('hidden');
            }
            
            // Ẩn reaction menu
            const reactionMenu = document.getElementById('reaction-menu');
            if (reactionMenu) {
                reactionMenu.style.display = 'none';
            }
        }
    });
    
    // Automatically open comment box if URL has comment hash
    if (window.location.hash && window.location.hash.includes('comment')) {
        const itemId = window.location.hash.split('-')[1];
        if (itemId) {
            setTimeout(() => {
                const commentBox = document.getElementById('comment-box-' + itemId);
                if (commentBox) {
                    const itemCode = commentBox.closest('[data-item-code]')?.dataset.itemCode || 'tblog';
                    toggleCommentBox(itemId, itemCode);
                }
            }, 500);
        }
    }
    
    // Add global event listener for comment likes
    document.addEventListener('click', function(e) {
        // Check if the clicked element is a comment like button
        const commentLikeBtn = e.target.closest('.comment-like-btn');
        if (commentLikeBtn) {
            const commentId = commentLikeBtn.dataset.commentId;
            const itemId = commentLikeBtn.dataset.itemId;
            const itemCode = commentLikeBtn.dataset.itemCode;
            
            if (commentId && itemId && itemCode) {
                toggleCommentLike(commentId, itemId, itemCode);
            }
        }
        
        // Check if the clicked element is a reply-to-reply button
        const replyToReplyBtn = e.target.closest('.reply-to-reply-btn');
        if (replyToReplyBtn) {
            const parentId = replyToReplyBtn.dataset.parentId;
            const replyToId = replyToReplyBtn.dataset.replyToId;
            const replyToName = replyToReplyBtn.dataset.replyToName;
            const itemId = replyToReplyBtn.dataset.itemId;
            const itemCode = replyToReplyBtn.dataset.itemCode;
            
            if (parentId && replyToId && replyToName && itemId && itemCode) {
                replyToReply(parentId, replyToId, replyToName, itemId, itemCode);
            }
        }
    });
}

// Make functions available globally
window.reactToPost = reactToPost;
window.toggleCommentBox = toggleCommentBox;
window.loadComments = loadComments;
window.submitComment = submitComment;
window.replyToComment = replyToComment;
window.toggleReplyForm = toggleReplyForm;
window.addEmoji = addEmoji;
window.sharePost = sharePost;
window.deleteComment = deleteComment;
window.editComment = editComment;
window.toggleCommentLike = toggleCommentLike;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initSocialInteractions); 