document.addEventListener('DOMContentLoaded', function () {
    // Khai báo các biến toàn cục để thay thế các template blade
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const bookUploadUrl = '/public/upload/avatar'; // URL upload ảnh sách
    const bookmarkUrl = '/front/book/bookmark'; // URL bookmark sách
    const loginUrl = '/front/login'; // URL trang đăng nhập
    
    // Ngăn Dropzone khởi tạo tự động
    Dropzone.autoDiscover = false;

    // BỎ các event listener cho dropdown user, để sử dụng cái đã thiết lập trong header.blade.php
    // KHÔNG ghi đè các event handlers từ header

    // Mobile menu toggle (chỉ khi không tồn tại từ trước)
    if (document.getElementById('mobile-menu-button') && !document.getElementById('mobile-menu-button')
        .hasAttribute('data-event-attached')) {
        document.getElementById('mobile-menu-button').setAttribute('data-event-attached', 'true');
        document.getElementById('mobile-menu-button').addEventListener('click', function () {
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenu) mobileMenu.classList.toggle('hidden');
        });
    }

    // Sort dropdown toggle
    if (document.getElementById('sort-button')) {
        document.getElementById('sort-button').addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = document.getElementById('sort-dropdown');
            if (dropdown) dropdown.classList.toggle('active');
        });
    }

    // Filter dropdown toggle
    if (document.getElementById('filter-button')) {
        document.getElementById('filter-button').addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = document.getElementById('filter-dropdown');
            if (dropdown) dropdown.classList.toggle('active');
        });
    }

    // Category filter active state
    const categoryFilters = document.querySelectorAll('.category-filter');
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function () {
            categoryFilters.forEach(f => f.classList.remove('active', 'bg-blue-500',
                'text-white'));
            this.classList.add('active', 'bg-blue-500', 'text-white');
        });
    });

    // Sidebar toggle for mobile
    if (document.getElementById('sidebar-toggle')) {
        document.getElementById('sidebar-toggle').addEventListener('click', function () {
            const sidebar = document.getElementById('left-sidebar');
            const mainContent = document.getElementById('main-content');
            if (sidebar) sidebar.classList.toggle('collapsed');
            if (mainContent) mainContent.classList.toggle('expanded');
        });
    }

    // Scroll to top button
    const scrollToTopBtn = document.getElementById('scroll-to-top');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.remove('opacity-0', 'invisible');
                scrollToTopBtn.classList.add('opacity-100', 'visible');
            } else {
                scrollToTopBtn.classList.remove('opacity-100', 'visible');
                scrollToTopBtn.classList.add('opacity-0', 'invisible');
            }
        });

        scrollToTopBtn.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Quick View Modal functionality
    const quickViewModal = document.getElementById('quick-view-modal');
    const closeQuickViewBtn = document.getElementById('close-quick-view');
    const modalLoading = document.getElementById('modal-loading');
    const modalContent = document.getElementById('modal-content');

    // Get all quick view buttons
    const quickViewBtns = document.querySelectorAll('.quick-view-btn');

    // Function to create action buttons
    function createActionButtons(data) {
        const actionContainer = document.getElementById('modal-book-action-buttons');
        actionContainer.innerHTML = '';

        // Read button - always show
        const readBtn = document.createElement('a');
        readBtn.href = `/front/book/read/${data.id}`;
        readBtn.className =
            'bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md flex items-center';
        readBtn.innerHTML = '<i class="fas fa-book-open mr-2"></i> Đọc sách';
        actionContainer.appendChild(readBtn);

        // Audio button - only if available
        if (data.has_audio) {
            const audioBtn = document.createElement('a');
            audioBtn.href = `/front/book/show/${data.slug}?format=audio`;
            audioBtn.className =
                'bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md flex items-center';
            audioBtn.innerHTML = '<i class="fas fa-headphones mr-2"></i> Nghe audio';
            actionContainer.appendChild(audioBtn);
        }

        // Details button - always show
        const detailsBtn = document.createElement('a');
        detailsBtn.href = `/front/book/show/${data.slug}`;
        detailsBtn.className =
            'bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-2 rounded-md flex items-center';
        detailsBtn.innerHTML = '<i class="fas fa-info-circle mr-2"></i> Chi tiết';
        actionContainer.appendChild(detailsBtn);

        // Bookmark button - only if user is logged in
        if (data.can_bookmark) {
            const bookmarkBtn = document.createElement('button');
            bookmarkBtn.type = 'button';
            bookmarkBtn.className =
                'bookmark-btn bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-2 rounded-md flex items-center';
            bookmarkBtn.setAttribute('data-book-id', data.id);
            bookmarkBtn.innerHTML = data.is_bookmarked ?
                '<i class="fas fa-bookmark mr-2"></i> Đã lưu' :
                '<i class="far fa-bookmark mr-2"></i> Lưu lại';

            // Add click handler for bookmark button
            bookmarkBtn.addEventListener('click', function () {
                const bookId = this.getAttribute('data-book-id');
                fetch('/front/book/bookmark', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        book_id: bookId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            // Toggle the bookmark icon and text
                            if (data.action === 'added') {
                                bookmarkBtn.innerHTML =
                                    '<i class="fas fa-bookmark mr-2"></i> Đã lưu';
                            } else {
                                bookmarkBtn.innerHTML =
                                    '<i class="far fa-bookmark mr-2"></i> Lưu lại';
                            }
                        } else {
                            alert('Có lỗi xảy ra. Vui lòng thử lại sau.');
                        }
                    })
                    .catch(error => {
                        console.error('Error bookmarking:', error);
                        alert('Có lỗi xảy ra. Vui lòng thử lại sau.');
                    });
            });

            actionContainer.appendChild(bookmarkBtn);
        }
    }

    // Add click event to each quick view button
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Get the book card
            const bookCard = this.closest('.book-card');

            // Get book ID for AJAX request
            const bookId = bookCard.getAttribute('data-book-id');

            // Temporarily update modal with basic information from card
            const bookTitle = bookCard.querySelector('h3').innerText;
            const bookAuthor = bookCard.querySelector('p').innerText;
            const bookCover = bookCard.querySelector('img').getAttribute('src');

            document.getElementById('modal-book-title').innerText = bookTitle;
            document.getElementById('modal-book-author').innerText = bookAuthor;
            document.getElementById('modal-book-cover').setAttribute('src', bookCover);

            // Clear previous content
            document.getElementById('modal-book-stars').innerHTML = '';
            document.getElementById('modal-book-rating').innerText = '';
            document.getElementById('modal-book-tags').innerHTML = '';
            document.getElementById('modal-book-summary').innerText = 'Đang tải...';
            document.getElementById('modal-book-action-buttons').innerHTML = '';

            // Show modal with loading state
            quickViewModal.classList.add('active');
            modalLoading.classList.remove('hidden');
            modalContent.classList.add('opacity-50');

            // Prevent scrolling on the body
            document.body.style.overflow = 'hidden';

            // Fetch book details with AJAX
            fetch(`/api/books/${bookId}/details`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Book data:', data); // For debugging

                    // Hide loading spinner, show content
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('opacity-50');

                    // Update modal content with actual data
                    document.getElementById('modal-book-title').innerText = data
                        .title || bookTitle;
                    document.getElementById('modal-book-author').innerText = data
                        .author || bookAuthor;
                    document.getElementById('modal-book-cover').setAttribute('src', data
                        .photo || bookCover);

                    // Update rating and stars
                    const starsContainer = document.getElementById('modal-book-stars');
                    starsContainer.innerHTML = '';

                    const rating = data.vote_average || 0;
                    document.getElementById('modal-book-rating').innerText =
                        `${parseFloat(rating).toFixed(1)} (${data.vote_count || 0} đánh giá)`;

                    // Generate stars
                    for (let i = 1; i <= 5; i++) {
                        const star = document.createElement('i');
                        if (i <= rating) {
                            star.className = 'fas fa-star';
                        } else if (i - 0.5 <= rating) {
                            star.className = 'fas fa-star-half-alt';
                        } else {
                            star.className = 'far fa-star';
                        }
                        starsContainer.appendChild(star);
                    }

                    // Update view count
                    if (data.views !== undefined) {
                        document.getElementById('modal-book-views').innerHTML =
                            `<i class="fas fa-eye mr-1"></i> ${data.views} lượt xem`;
                    }

                    // Update tags
                    const tagsContainer = document.getElementById('modal-book-tags');
                    tagsContainer.innerHTML = '';

                    if (data.tags && data.tags.length > 0) {
                        data.tags.forEach(tag => {
                            const tagSpan = document.createElement('span');
                            tagSpan.className =
                                'bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs';
                            tagSpan.innerText = tag.title;
                            tagsContainer.appendChild(tagSpan);
                        });
                    } else if (data.book_type) {
                        const tagSpan = document.createElement('span');
                        tagSpan.className =
                            'bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs';
                        tagSpan.innerText = data.book_type;
                        tagsContainer.appendChild(tagSpan);
                    } else {
                        const tagSpan = document.createElement('span');
                        tagSpan.className = 'text-gray-500 text-sm';
                        tagSpan.innerText = 'Không có thẻ';
                        tagsContainer.appendChild(tagSpan);
                    }

                    // Update summary
                    document.getElementById('modal-book-summary').innerText = data
                        .summary || 'Không có mô tả cho sách này.';

                    // Update metadata fields
                    const updateMetadataField = (containerId, valueId, value) => {
                        const container = document.getElementById(containerId);
                        if (value) {
                            document.getElementById(valueId).innerText = value;
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    };

                    updateMetadataField('modal-book-publisher-container',
                        'modal-book-publisher', data.publisher);
                    updateMetadataField('modal-book-year-container', 'modal-book-year',
                        data.published_year);
                    updateMetadataField('modal-book-pages-container',
                        'modal-book-pages', data.pages);
                    updateMetadataField('modal-book-language-container',
                        'modal-book-language', data.language);

                    // Create action buttons
                    createActionButtons(data);
                })
                .catch(error => {
                    console.error('Error fetching book details:', error);

                    // Hide loading spinner, show content with basic info
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('opacity-50');

                    // Show error message
                    document.getElementById('modal-book-summary').innerText =
                        'Không thể tải thông tin sách. Vui lòng thử lại sau.';

                    // Create minimal action buttons with available data
                    const actionContainer = document.getElementById(
                        'modal-book-action-buttons');
                    actionContainer.innerHTML = '';

                    // Details button - always show
                    const detailsBtn = document.createElement('a');
                    detailsBtn.href =
                        `/front/book/show/${bookId}`; // Fallback to using ID
                    detailsBtn.className =
                        'bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-2 rounded-md flex items-center';
                    detailsBtn.innerHTML =
                        '<i class="fas fa-info-circle mr-2"></i> Chi tiết';
                    actionContainer.appendChild(detailsBtn);
                });
        });
    });

    // Close modal when clicking the close button
    if (closeQuickViewBtn) {
        closeQuickViewBtn.addEventListener('click', function () {
            quickViewModal.classList.remove('active');
            document.body.style.overflow = 'auto';

            // Clear content after closing
            setTimeout(() => {
                document.getElementById('modal-book-summary').innerText = '';
                document.getElementById('modal-book-action-buttons').innerHTML = '';
            }, 300);
        });
    }

    // Close modal when clicking outside the modal content
    if (quickViewModal) {
        quickViewModal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && quickViewModal && quickViewModal.classList.contains('active')) {
            quickViewModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });

    // ===== BOOK CREATION MODAL SCRIPT =====
    // Xử lý modal đăng sách mới
    const createBookBtn = document.getElementById('create-book-btn');
    const createBookModal = document.getElementById('create-book-modal');
    const closeCreateBookModalBtn = document.getElementById('close-create-book-modal');
    const cancelCreateBookBtn = document.getElementById('cancel-create-book');
    const createBookForm = document.getElementById('create-book-form');
    const documentUpload = document.getElementById('document-upload');
    const selectedFilesContainer = document.getElementById('selected-files');

    // Mở modal đăng sách
    if (createBookBtn && createBookModal) {
        let dropzoneInitialized = false;
        
        createBookBtn.addEventListener('click', function () {
            createBookModal.classList.remove('hidden');
            document.body.classList.add('modal-open');
            
            // Chỉ khởi tạo Dropzone khi cần thiết
            if (!dropzoneInitialized) {
                setTimeout(function() {
                    setupBookDropzone();
                    setupTagsSelect();
                    dropzoneInitialized = true;
                }, 100);
            }
        });
    }

    // Đóng modal đăng sách
    function closeCreateBookModal() {
        createBookModal.classList.add('hidden');
        document.body.classList.remove('modal-open');

        // Reset form
        if (createBookForm) createBookForm.reset();

        // Reset Dropzone safely
        try {
            if (window.bookImageDropzone) {
                if (typeof window.bookImageDropzone.removeAllFiles === 'function') {
                    window.bookImageDropzone.removeAllFiles(true);
                } else {
                    // Phương pháp thay thế
                    const dropzoneElement = document.getElementById('bookImageDropzone');
                    if (dropzoneElement) {
                        while (dropzoneElement.firstChild) {
                            dropzoneElement.removeChild(dropzoneElement.firstChild);
                        }
                    }
                }
            }
        } catch (e) {
            console.error('Lỗi khi reset dropzone:', e);
        }

        // Reset TomSelect
        if (window.bookTagsSelect) {
            window.bookTagsSelect.clear();
        }

        // Reset selected files
        if (selectedFilesContainer) {
            selectedFilesContainer.innerHTML = '';
        }

        // Reset hidden input
        document.getElementById('uploadedBookImage').value = '';
    }

    if (closeCreateBookModalBtn && createBookModal) {
        closeCreateBookModalBtn.addEventListener('click', closeCreateBookModal);
    }

    if (cancelCreateBookBtn && createBookModal) {
        cancelCreateBookBtn.addEventListener('click', closeCreateBookModal);
    }

    // Đóng modal khi click ngoài content
    if (createBookModal) {
        createBookModal.addEventListener('click', function (e) {
            if (e.target === this || e.target.classList.contains('fixed')) {
                closeCreateBookModal();
            }
        });
    }

    // Hiển thị tên file khi người dùng chọn file
    if (documentUpload) {
        documentUpload.addEventListener('change', function () {
            if (selectedFilesContainer) {
                selectedFilesContainer.innerHTML = '';

                if (this.files.length > 0) {
                    const fileList = document.createElement('ul');
                    fileList.className = 'list-disc pl-5 text-left';

                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        const fileItem = document.createElement('li');
                        fileItem.className = 'text-blue-600';
                        fileItem.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
                        fileList.appendChild(fileItem);
                    }

                    selectedFilesContainer.appendChild(fileList);
                }
            }
        });
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Xử lý drag & drop cho khu vực tải lên tài liệu
    const dropArea = document.querySelector('.border-dashed');
    if (dropArea && documentUpload) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, function () {
                this.classList.add('border-blue-500', 'bg-blue-50');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function () {
                this.classList.remove('border-blue-500', 'bg-blue-50');
            });
        });

        dropArea.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            documentUpload.files = files;

            // Trigger change event
            const event = new Event('change');
            documentUpload.dispatchEvent(event);
        });
    }

    // Submit form với progress indicator
    if (createBookForm) {
        createBookForm.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');

            // Validate form
            if (!this.checkValidity()) {
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý...';
            }

            const copyrightConfirmation = document.getElementById('copyright-confirmation');
            
            if (!copyrightConfirmation.checked) {
                e.preventDefault();
                alert('Vui lòng xác nhận bản quyền trước khi đăng sách.');
                return false;
            }
            
            // Tiếp tục submit form nếu đã check xác nhận bản quyền
        });
    }

    // Xử lý nút yêu thích sách
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const bookId = this.getAttribute('data-id');
            const itemCode = this.getAttribute('data-code');
            const button = this;

            fetch(bookmarkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    item_id: bookId,
                    item_code: itemCode
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật giao diện
                        if (data.isBookmarked) {
                            button.classList.remove('text-gray-500', 'hover:text-gray-700', 'bg-gray-50');
                            button.classList.add('text-red-500', 'hover:text-red-700', 'bg-red-50');
                            button.querySelector('i').classList.remove('far');
                            button.querySelector('i').classList.add('fas');
                        } else {
                            button.classList.remove('text-red-500', 'hover:text-red-700', 'bg-red-50');
                            button.classList.add('text-gray-500', 'hover:text-gray-700', 'bg-gray-50');
                            button.querySelector('i').classList.remove('fas');
                            button.querySelector('i').classList.add('far');
                        }
                    } else {
                        // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
                        if (data.msg === 'Bạn phải đăng nhập') {
                            window.location.href = loginUrl;
                        } else {
                            alert(data.msg || 'Có lỗi xảy ra, vui lòng thử lại sau.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi xử lý yêu cầu.');
                });
        });
    });

    // Thêm hiệu ứng hover cho các nút sắp xếp
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function () {
            if (!this.classList.contains('bg-blue-500')) {
                this.classList.add('shadow-sm');
            }
        });

        btn.addEventListener('mouseleave', function () {
            this.classList.remove('shadow-sm');
        });
    });

    // Xử lý nút tải xuống tài liệu
    const downloadResourcesBtns = document.querySelectorAll('.download-resources-btn');
    const downloadResourcesModal = document.getElementById('download-resources-modal');
    const closeDownloadResourcesBtn = document.getElementById('close-download-resources-modal');
    const cancelDownloadResourcesBtn = document.getElementById('cancel-download-resources');
    const resourcesList = document.getElementById('resources-list');

    // Mở modal tải xuống tài liệu
    downloadResourcesBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const bookId = this.getAttribute('data-id');
            downloadResourcesModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Fetch tài liệu của sách
            fetch(`/api/books/${bookId}/resources`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hiển thị danh sách tài liệu
                    displayResources(data.resources, bookId);
                })
                .catch(error => {
                    console.error('Lỗi khi tải tài liệu:', error);
                    resourcesList.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    Có lỗi xảy ra khi tải danh sách tài liệu. Vui lòng thử lại sau.
                                </div>
                            `;
                });
        });
    });

    // Đóng modal tải xuống tài liệu
    function closeDownloadResourcesModal() {
        downloadResourcesModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        resourcesList.innerHTML = `
                    <div class="text-center py-8">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Đang tải...</span>
                        </div>
                        <p class="mt-2 text-gray-600">Đang tải danh sách tài liệu...</p>
                    </div>
                `;
    }

    if (closeDownloadResourcesBtn) {
        closeDownloadResourcesBtn.addEventListener('click', closeDownloadResourcesModal);
    }

    if (cancelDownloadResourcesBtn) {
        cancelDownloadResourcesBtn.addEventListener('click', closeDownloadResourcesModal);
    }

    // Đóng modal khi click ngoài content
    if (downloadResourcesModal) {
        downloadResourcesModal.addEventListener('click', function (e) {
            if (e.target === this || e.target.classList.contains('fixed')) {
                closeDownloadResourcesModal();
            }
        });
    }

    // Hiển thị danh sách tài liệu
    function displayResources(resources, bookId) {
        if (!resources || resources.length === 0) {
            resourcesList.innerHTML = `
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            Không có tài liệu nào khả dụng cho sách này.
                        </div>
                    `;
            return;
        }

        let html = '<div class="rounded-lg border border-gray-200 overflow-hidden">';
        html += '<ul class="divide-y divide-gray-200">';

        resources.forEach(resource => {
            let icon = resource.icon_class || 'fas fa-file';
            let bgColor = 'bg-gray-100';

            // Xác định màu nền dựa trên icon
            if (icon.includes('pdf')) {
                bgColor = 'bg-red-100';
            } else if (icon.includes('word')) {
                bgColor = 'bg-blue-100';
            } else if (icon.includes('excel')) {
                bgColor = 'bg-green-100';
            } else if (icon.includes('powerpoint')) {
                bgColor = 'bg-orange-100';
            } else if (icon.includes('image')) {
                bgColor = 'bg-purple-100';
            } else if (icon.includes('audio')) {
                bgColor = 'bg-pink-100';
            } else if (icon.includes('video')) {
                bgColor = 'bg-indigo-100';
            } else if (icon.includes('archive')) {
                bgColor = 'bg-yellow-100';
            } else if (icon.includes('code')) {
                bgColor = 'bg-cyan-100';
            } else if (icon.includes('alt')) {
                bgColor = 'bg-gray-100';
            }

            // Nếu là link YouTube, hiển thị icon YouTube
            if (resource.link_code === 'youtube') {
                icon = 'fab fa-youtube';
                bgColor = 'bg-red-100';
            }

            // Hiển thị định dạng file dễ đọc
            let fileType = resource.file_type || 'Không xác định';
            if (fileType.includes('application/pdf')) {
                fileType = 'PDF Document';
            } else if (fileType.includes('application/msword') || fileType.includes('application/vnd.openxmlformats-officedocument.wordprocessingml.document')) {
                fileType = 'Word Document';
            } else if (fileType.includes('application/vnd.ms-excel') || fileType.includes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
                fileType = 'Excel Spreadsheet';
            } else if (fileType.includes('application/vnd.ms-powerpoint') || fileType.includes('application/vnd.openxmlformats-officedocument.presentationml.presentation')) {
                fileType = 'PowerPoint Presentation';
            } else if (fileType.includes('image/')) {
                fileType = fileType.replace('image/', 'Hình ảnh ');
            } else if (fileType.includes('audio/')) {
                fileType = fileType.replace('audio/', 'Âm thanh ');
            } else if (fileType.includes('video/')) {
                fileType = fileType.replace('video/', 'Video ');
            }

            // Sử dụng download_url nếu có, không thì fallback về url
            const downloadUrl = resource.download_url || resource.url;

            html += `
                        <li class="p-4 hover:bg-gray-50">
                            <div class="flex items-start space-x-4">
                                <div class="${bgColor} text-gray-700 p-2 rounded-lg">
                                    <i class="${icon} text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        ${resource.title || 'Tài liệu không có tiêu đề'}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        ${fileType}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        ${resource.file_size ? formatFileSize(resource.file_size) : 'Không có thông tin kích thước'}
                                    </p>
                                </div>
                                <div>
                                    ${resource.link_code === 'youtube' ?
                    `<a href="${resource.url}" target="_blank" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            <i class="fas fa-external-link-alt mr-1"></i> Xem trên YouTube
                                        </a>` :
                    (resource.is_downloadable !== false ?
                        `<a href="${downloadUrl}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium" download>
                                                <i class="fas fa-download mr-1"></i> Tải xuống
                                            </a>` :
                        `<a href="${resource.url}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                <i class="fas fa-external-link-alt mr-1"></i> Mở liên kết
                                            </a>`
                    )
                }
                                </div>
                            </div>
                        </li>
                    `;
        });

        html += '</ul></div>';
        resourcesList.innerHTML = html;
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Đảm bảo các modal hoạt động đúng khi click bên ngoài
    document.addEventListener('click', function(e) {
        // Kiểm tra click bên ngoài modal create-book
        const createBookModal = document.getElementById('create-book-modal');
        if (createBookModal && !createBookModal.classList.contains('hidden')) {
            const modalContent = createBookModal.querySelector('.modal-content');
            if (modalContent && !modalContent.contains(e.target) && e.target !== modalContent) {
                // Kiểm tra nếu click vào backdrop (không phải vào content)
                if (createBookModal.contains(e.target)) {
                    createBookModal.classList.add('hidden');
                    document.body.classList.remove('modal-open');
                }
            }
        }
        
        // Kiểm tra click bên ngoài modal download-resources
        const downloadResourcesModal = document.getElementById('download-resources-modal');
        if (downloadResourcesModal && !downloadResourcesModal.classList.contains('hidden')) {
            const modalContent = downloadResourcesModal.querySelector('.modal-content');
            if (modalContent && !modalContent.contains(e.target) && e.target !== modalContent) {
                // Kiểm tra nếu click vào backdrop (không phải vào content)
                if (downloadResourcesModal.contains(e.target)) {
                    downloadResourcesModal.classList.add('hidden');
                    document.body.classList.remove('modal-open');
                }
            }
        }
        
        // Kiểm tra click bên ngoài modal quick-view
        const quickViewModal = document.getElementById('quick-view-modal');
        if (quickViewModal && quickViewModal.classList.contains('active')) {
            const modalContent = quickViewModal.querySelector('.inline-block');
            if (modalContent && !modalContent.contains(e.target) && e.target !== modalContent) {
                // Kiểm tra nếu click vào backdrop (không phải vào content)
                if (quickViewModal.contains(e.target)) {
                    quickViewModal.classList.remove('active');
                    document.body.classList.remove('modal-open');
                }
            }
        }
    });

    // Function to setup dropzone
    function setupBookDropzone() {
        console.log('Setting up Dropzone...');
        
        // Kiểm tra sự tồn tại của phần tử Dropzone
        const dropzoneEl = document.getElementById('bookImageDropzone');
        if (!dropzoneEl) {
            console.error('Không tìm thấy phần tử #bookImageDropzone');
            return;
        }
        
        console.log('Dropzone element found:', dropzoneEl);
        
        try {
            // Vấn đề Dropzone already attached - Trước khi tạo một Dropzone mới
            // cần kiểm tra và xóa các thuộc tính của Dropzone trước đó
            if (dropzoneEl.dropzone) {
                console.log('Phát hiện dropzone đã tồn tại, hủy bỏ instance cũ');
                dropzoneEl.dropzone.destroy();
                delete dropzoneEl.dropzone;
            }
            
            // Xóa tất cả các class bắt đầu bằng "dz-" để đảm bảo dropzone được khởi tạo mới hoàn toàn
            const dzClasses = Array.from(dropzoneEl.classList).filter(cls => cls.startsWith('dz-'));
            if (dzClasses.length > 0) {
                console.log('Xóa các class dropzone cũ:', dzClasses);
                dzClasses.forEach(cls => dropzoneEl.classList.remove(cls));
            }
            
            // Xóa tất cả các thẻ con đã được tạo bởi Dropzone
            while (dropzoneEl.firstChild) {
                dropzoneEl.removeChild(dropzoneEl.firstChild);
            }
            
            // Xóa instance cũ nếu có
            if (window.bookImageDropzone) {
                try {
                    if (typeof window.bookImageDropzone.destroy === 'function') {
                        window.bookImageDropzone.destroy();
                        console.log('Đã xóa Dropzone cũ');
                    }
                } catch (e) {
                    console.error('Lỗi khi hủy Dropzone cũ:', e);
                }
                window.bookImageDropzone = null;
            }

            // Đảm bảo rằng Dropzone.autoDiscover = false để tránh tự động khởi tạo
            if (typeof Dropzone === 'undefined') {
                console.error('Thư viện Dropzone không được tải');
                return;
            }
            
            Dropzone.autoDiscover = false;
            console.log('Dropzone.autoDiscover set to false');

            // Thay đổi một số thuộc tính CSS để đảm bảo Dropzone nhận được sự kiện click
            dropzoneEl.style.position = 'relative';
            dropzoneEl.style.zIndex = '50';
            dropzoneEl.style.pointerEvents = 'auto';
            
            // Tạo lại phần tử dropzone
            const bookUploadUrl = dropzoneEl.getAttribute('data-url') || '/public/upload/avatar';
            
            // Thêm sự kiện click trực tiếp để debug
            dropzoneEl.addEventListener('click', function(e) {
                console.log('Dropzone element clicked:', e);
            });

            // Tạo dropzone mới
            window.bookImageDropzone = new Dropzone(dropzoneEl, {
                url: bookUploadUrl,
                paramName: "photo",
                maxFilesize: 5,
                acceptedFiles: 'image/*',
                addRemoveLinks: true,
                dictDefaultMessage: "Kéo thả ảnh vào đây hoặc nhấp để chọn",
                dictRemoveFile: "Xóa ảnh",
                thumbnailWidth: 150,
                thumbnailHeight: 150,
                maxFiles: 1,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                clickable: true, // Đảm bảo dropzone nhận sự kiện click
                success: function (file, response) {
                    console.log('Upload success:', response);
                    if (response && response.link) {
                        document.getElementById('uploadedBookImage').value = response.link;
                    } else if (response && response.url) {
                        document.getElementById('uploadedBookImage').value = response.url;
                    } else {
                        console.error('Upload response lacks link or url:', response);
                        alert('Tải lên thành công nhưng không nhận được đường dẫn ảnh');
                    }
                },
                removedfile: function (file) {
                    document.getElementById('uploadedBookImage').value = '';
                    if (file.previewElement) {
                        file.previewElement.remove();
                    }
                },
                error: function (file, errorMessage) {
                    console.error("Dropzone error:", errorMessage);
                    console.log("File causing error:", file);
                    alert("Lỗi tải lên: " + (typeof errorMessage === 'string' ? errorMessage : JSON.stringify(errorMessage)));
                },
                init: function () {
                    console.log('Dropzone initialized successfully');
                    
                    const dropzone = this;

                    // Thêm sự kiện click trực tiếp cho nút "Browse..." trong Dropzone
                    setTimeout(function() {
                        const dzMessage = dropzoneEl.querySelector('.dz-message');
                        if (dzMessage) {
                            dzMessage.addEventListener('click', function(e) {
                                console.log('Dz-message clicked');
                                e.stopPropagation();
                                // Trigger click manually trên input file của Dropzone
                                const fileInput = dropzoneEl.querySelector('input[type=file]');
                                if (fileInput) {
                                    fileInput.click();
                                }
                            });
                        }
                    }, 100);

                    this.on("addedfile", function (file) {
                        console.log("File added:", file);
                    });

                    this.on("sending", function (file, xhr, formData) {
                        console.log("Sending file...");
                        formData.append("_token", csrfToken);

                        // Thêm event listener để theo dõi quá trình request
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4) {
                                console.log('Response status:', xhr.status);
                                console.log('Response text:', xhr.responseText);
                                if (xhr.status !== 200) {
                                    console.error('Server error:', xhr.status, xhr.statusText);
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        console.error('Server error details:', response);
                                    } catch (e) {
                                        console.error('Unable to parse response:', e);
                                    }
                                }
                            }
                        };
                    });

                    this.on("complete", function (file) {
                        console.log("Upload complete for file:", file.name);
                    });
                    
                    // Đảm bảo rằng input file trong dropzone có thể click được
                    const fileInputs = dropzoneEl.querySelectorAll('input[type=file]');
                    fileInputs.forEach(input => {
                        input.style.position = 'absolute';
                        input.style.top = '0';
                        input.style.left = '0';
                        input.style.width = '100%';
                        input.style.height = '100%';
                        input.style.opacity = '0';
                        input.style.cursor = 'pointer';
                        input.style.zIndex = '100';
                    });
                }
            });
            
            console.log('Dropzone setup complete');
            
            // Thêm sự kiện click cho toàn bộ container để debug
            const modalContent = document.querySelector('.modal-content');
            if (modalContent) {
                modalContent.addEventListener('click', function(e) {
                    console.log('Modal content clicked at:', e.clientX, e.clientY);
                    console.log('Target:', e.target);
                });
            }
            
        } catch (err) {
            console.error('Dropzone initialization error:', err);
        }
    }

    // Function to setup tags select
    function setupTagsSelect() {
        console.log('Setting up TomSelect for tags...');
        if (!document.getElementById('book-tags')) {
            console.log('Tags element not found');
            return;
        }

        // Khởi tạo TomSelect cho tags
        setTimeout(() => {
            if (!window.bookTagsSelect && document.getElementById('book-tags')) {
                console.log('Initializing TomSelect...');
                try {
                    window.bookTagsSelect = new TomSelect('#book-tags', {
                        maxItems: null,
                        valueField: 'id',
                        labelField: 'title',
                        searchField: 'title',
                        create: function (input) {
                            console.log('Creating new tag:', input);
                            return {
                                id: 'new_' + input,
                                title: input
                            };
                        },
                        createFilter: function (input) {
                            return input.length >= 2;
                        },
                        createOnBlur: true,
                        plugins: ['remove_button'],
                        onInitialize: function () {
                            console.log('TomSelect initialized successfully');
                        }
                    });
                    console.log('TomSelect setup complete');
                } catch (err) {
                    console.error('TomSelect initialization error:', err);
                }
            }
        }, 200);
    }

    // Open Quick View Modal
    function openQuickViewModal(bookId) {
        const modal = document.getElementById('quick-view-modal');
        const modalLoading = document.getElementById('modal-loading');
        const modalContent = document.getElementById('modal-content');
        
        if (modal && modalLoading && modalContent) {
            // Hiển thị modal và loading
            modal.classList.add('active');
            modalLoading.classList.remove('hidden');
            modalContent.style.opacity = '0.5';
            document.body.classList.add('modal-open'); // Add modal-open class
            
            // ... rest of existing openQuickViewModal code ...
        }
    }
});
