@extends('frontend.layouts.master')
@section('css')
    <link rel="stylesheet" href="{{ asset('frontend/css/book/show.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
    <div class="mx-auto py-6">
        <div class="book-detail-container">
            @include('frontend.book.partials._book_info')
        </div>

        @include('frontend.book.partials._book_reviews')
    </div>
@endsection

@section('scripts')
    <script>
        // Truyền dữ liệu từ PHP sang JavaScript
        const bookData = {
            id: {{ $book->id }},
            title: "{{ addslashes($book->title) }}",
            averageRating: {{ number_format($book->average_rating, 1) }},
            ratingCount: {{ $book->rating_count ?? 0 }}
        };
        
        const csrfToken = "{{ csrf_token() }}";
        const siteUrl = "{{ url('/') }}";
        const bookmarkUrl = "{{ route('front.book.bookmark') }}";
        const userLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        const currentUserId = {{ Auth::id() ?? 'null' }};
        const hasResources = {{ isset($hasResources) && $hasResources ? 'true' : 'false' }};
    </script>
    
    <script src="{{ asset('frontend/js/book/_show_book.js') }}"></script>
    <script src="{{ asset('js/book-mention.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo mention cho tất cả các textarea comment
            document.querySelectorAll('.comment-textarea').forEach(textarea => {
                new BookMention(textarea);
            });
        });
    </script>
    <script>
        function showToast(message, type = 'success') {
            // Tạo phần tử toast
            const toast = document.createElement('div');
            toast.className = `toast-notification fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;

            // Thêm nội dung
            toast.innerHTML = `
                <div class="flex items-center">
                    <span class="mr-2">${type === 'success' ? '✅' : '❌'}</span>
                    <span>${message}</span>
                </div>
            `;

            // Thêm vào body
            document.body.appendChild(toast);

            // Hiệu ứng hiển thị
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Tự động biến mất sau 3 giây
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }
    </script>
    <script src="{{ asset('modules/tuongtac/social-interactions.js') }}"></script>
    <script>
        // Mới thêm: Các hàm tải và hiển thị tài liệu
        function loadBookResources() {
            const bookId = {{ $book->id }};
            const resourcesContainer = document.getElementById('book-resources-container');

            if (!resourcesContainer) return;

            fetch(`/api/books/${bookId}/resources`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Không thể tải tài liệu');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.resources && data.resources.length > 0) {
                        displayResources(data.resources, resourcesContainer);
                    } else {
                        resourcesContainer.innerHTML =
                            '<p class="text-gray-500 text-center py-4">Không có tài liệu đính kèm.</p>';
                    }
                })
                .catch(error => {
                    console.error('Lỗi khi tải tài liệu:', error);
                    resourcesContainer.innerHTML =
                        '<p class="text-red-500 text-center py-4">Có lỗi xảy ra khi tải tài liệu.</p>';
                });
        }

        function displayResources(resources, container) {
            container.innerHTML = '';

            resources.forEach(resource => {
                const resourceElement = document.createElement('div');
                resourceElement.className =
                    'resource-item';

                // Xác định loại tài liệu
                let fileIcon = resource.icon_class || 'fas fa-file';

                // Tạo URL tải xuống
                let downloadUrl = '';
                if (resource.download_url) {
                    downloadUrl = resource.download_url;
                } else if (resource.path) {
                    downloadUrl = resource.path;
                } else if (resource.file_path) {
                    downloadUrl = resource.file_path;
                } else if (resource.url) {
                    downloadUrl = resource.url;
                } else {
                    downloadUrl = `/resource/download?id=${resource.id}`;
                }

                resourceElement.innerHTML = `
                    <div class="flex items-center">
                        <i class="${fileIcon} text-lg text-gray-600 mr-2"></i>
                        <div>
                            <div class="font-medium">${resource.title || resource.file_name || 'Tài liệu'}</div>
                            <div class="text-sm text-gray-500">${resource.file_name || ''}</div>
                        </div>
                    </div>
                    <a href="${downloadUrl}" 
                       class="download-link"
                       target="${resource.link_code === 'youtube' ? '_blank' : '_self'}"
                       ${resource.link_code !== 'youtube' ? 'download' : ''}>
                        <i class="fas fa-download mr-1"></i> Tải xuống
                    </a>
                `;

                container.appendChild(resourceElement);
            });
        }

        // Gọi hàm tải tài liệu khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            // Thêm sự kiện cho tab tài liệu
            const tabsButtons = document.querySelectorAll('.book-tab');
            if (tabsButtons) {
                tabsButtons.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const tabId = this.getAttribute('data-tab');
                        if (tabId === 'resources') {
                            loadBookResources();
                        }
                    });
                });
            }
        });
    </script>
@endsection
