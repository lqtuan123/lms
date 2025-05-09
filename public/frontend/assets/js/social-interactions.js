/**
 * Social Interactions JavaScript Module
 * Provides functions for likes, comments, and shares
 */

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
    if (!document.getElementById('reaction-menu')) {
        const reactionMenu = document.createElement('div');
        reactionMenu.id = 'reaction-menu';
        reactionMenu.className = 'reaction-menu hidden';
        reactionMenu.style.cssText = 'position:absolute; background:white; border-radius:24px; box-shadow:0 2px 8px rgba(0,0,0,0.2); padding:8px; display:flex; z-index:1000;';
        
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
                e.stopPropagation();
                const itemId = reactionMenu.dataset.itemId;
                const itemCode = reactionMenu.dataset.itemCode;
                reactToPost(itemId, itemCode, reaction.type);
                hideReactionMenu();
            });
            
            reactionMenu.appendChild(btn);
        });
        
        document.body.appendChild(reactionMenu);
        
        // Close menu when clicking outside
        document.addEventListener('click', () => {
            hideReactionMenu();
        });
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
    
    // Show menu
    reactionMenu.classList.remove('hidden');
}

/**
 * Hide reaction menu
 */
function hideReactionMenu() {
    const reactionMenu = document.getElementById('reaction-menu');
    if (reactionMenu) {
        reactionMenu.classList.add('hidden');
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
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';
    
    // Get like button and count elements
    const likeBtn = document.getElementById('like-btn-' + itemId);
    const likeCount = document.getElementById('like-count-' + itemId);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
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
    .then(response => response.json())
    .then(data => {
        spinner.style.display = 'none';
        
        if (data.success) {
            // Calculate total reactions
            let totalReactions = 0;
            for (const key in data.reactions) {
                totalReactions += data.reactions[key];
            }
            
            // Update like count
            if (likeCount) {
                likeCount.textContent = totalReactions;
            }
            
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
        } else {
            // Handle login redirect
            window.location.href = `/front/login`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        spinner.style.display = 'none';
    });
}

/**
 * Toggle comment box visibility
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function toggleCommentBox(itemId, itemCode) {
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
        
        commentBox.style.display = 'block';
        
        // Focus on comment input
        const commentInput = document.getElementById('comment-input-' + itemId);
        if (commentInput) {
            commentInput.focus();
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
function loadComments(itemId, itemCode) {
    const commentsContainer = document.getElementById('comments-container-' + itemId);
    if (!commentsContainer) return;
    
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
    document.querySelectorAll('.comment-dropdown-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('hidden');
        });
    });
}

/**
 * Submit a new comment
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function submitComment(itemId, itemCode) {
    const commentInput = document.getElementById('comment-input-' + itemId);
    if (!commentInput) return;
    
    const content = commentInput.value.trim();
    if (!content) return;
    
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
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
            
            // Update comment count in the UI
            const commentCountEl = document.querySelector(`#item-${itemId} .fa-comment`).nextElementSibling;
            if (commentCountEl) {
                const currentCount = parseInt(commentCountEl.textContent) || 0;
                commentCountEl.textContent = currentCount + 1;
            }
        } else {
            if (data.msg === 'chưa đăng nhập') {
                window.location.href = `/front/login`;
            } else {
                alert(data.msg);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        spinner.style.display = 'none';
    });
}

/**
 * Reply to a comment
 * 
 * @param {number} parentId - ID of the parent comment
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 */
function replyToComment(parentId, itemId, itemCode) {
    const replyInput = document.getElementById('reply-input-' + parentId);
    if (!replyInput) return;
    
    const content = replyInput.value.trim();
    if (!content) return;
    
    const spinner = document.getElementById('spinner');
    spinner.style.display = 'block';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
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
            parent_id: parentId
        })
    })
    .then(response => response.json())
    .then(data => {
        spinner.style.display = 'none';
        
        if (data.status) {
            // Clear input
            replyInput.value = '';
            
            // Reload comments to show the new reply
            loadComments(itemId, itemCode);
            
            // Update reply count if needed
        } else {
            if (data.msg === 'chưa đăng nhập') {
                window.location.href = `/front/login`;
            } else {
                alert(data.msg);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        spinner.style.display = 'none';
    });
}

/**
 * Add emoji to comment
 * 
 * @param {number} itemId - ID of the content item
 */
function addEmoji(itemId) {
    const emojis = ['😀', '😄', '😊', '🙂', '😍', '😎', '👍', '❤️', '🎉', '👏'];
    const commentInput = document.getElementById('comment-input-' + itemId);
    if (!commentInput) return;
    
    // Create emoji picker if it doesn't exist
    let emojiPicker = document.getElementById('emoji-picker');
    if (!emojiPicker) {
        emojiPicker = document.createElement('div');
        emojiPicker.id = 'emoji-picker';
        emojiPicker.className = 'bg-white border border-gray-200 rounded-lg p-2 shadow-lg fixed z-50 hidden';
        emojiPicker.style.width = '200px';
        
        // Add emojis to picker
        let emojiContent = '<div class="flex flex-wrap">';
        emojis.forEach(emoji => {
            emojiContent += `<button class="emoji-btn p-1 text-xl hover:bg-gray-100 rounded">${emoji}</button>`;
        });
        emojiContent += '</div>';
        emojiPicker.innerHTML = emojiContent;
        
        document.body.appendChild(emojiPicker);
        
        // Add click handlers to emojis
        emojiPicker.querySelectorAll('.emoji-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const activeInput = document.querySelector('.emoji-active-input');
                if (activeInput) {
                    activeInput.value += this.textContent;
                    activeInput.focus();
                }
                emojiPicker.classList.add('hidden');
            });
        });
        
        // Close picker when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#emoji-picker') && !e.target.classList.contains('emoji-trigger')) {
                emojiPicker.classList.add('hidden');
            }
        });
    }
    
    // Position and show the picker
    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();
    emojiPicker.style.top = (window.scrollY + rect.top - 150) + 'px';
    emojiPicker.style.left = (rect.left) + 'px';
    emojiPicker.classList.toggle('hidden');
    
    // Mark this input as active
    document.querySelectorAll('.comment-input').forEach(input => {
        input.classList.remove('emoji-active-input');
    });
    commentInput.classList.add('emoji-active-input');
}

/**
 * Share a post
 * 
 * @param {number} itemId - ID of the content item
 * @param {string} itemCode - Type of content (e.g., 'tblog', 'book')
 * @param {string} slug - URL slug for the content
 */
function sharePost(itemId, itemCode, slug) {
    // Create share URL based on content type
    let shareUrl;
    
    switch (itemCode) {
        case 'tblog':
            shareUrl = window.location.origin + '/tblogs/show/' + slug;
            break;
        case 'book':
            shareUrl = window.location.origin + '/front/book/' + itemId;
            break;
        default:
            shareUrl = window.location.href;
    }
    
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/front/share', {
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
        // Update share count in UI if needed
        const shareCountEl = document.querySelector(`#item-${itemId} .share-count`);
        if (shareCountEl && data.count) {
            shareCountEl.textContent = data.count + ' lượt chia sẻ';
        }
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
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
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
                
                // Update comment count
                const commentCountEl = document.querySelector(`#item-${itemId} .fa-comment`).nextElementSibling;
                if (commentCountEl) {
                    const currentCount = parseInt(commentCountEl.textContent) || 0;
                    if (currentCount > 0) {
                        commentCountEl.textContent = currentCount - 1;
                    }
                }
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
 * Initialize social interactions
 */
function initSocialInteractions() {
    initializeSpinner();
    initializeReactionMenu();
    
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
    
    // Initialize dropdown menus
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
    });
    
    // Handle ESC key to close popups
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
            hideReactionMenu();
        }
    });
}

// Make functions available globally
window.reactToPost = reactToPost;
window.toggleCommentBox = toggleCommentBox;
window.loadComments = loadComments;
window.submitComment = submitComment;
window.replyToComment = replyToComment;
window.addEmoji = addEmoji;
window.sharePost = sharePost;
window.deleteComment = deleteComment;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initSocialInteractions); 