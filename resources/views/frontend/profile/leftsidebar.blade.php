<!-- Left Sidebar -->

<div class="bg-white rounded-lg shadow-sm p-4">
    <!-- Nút đóng/mở sidebar (chỉ hiển thị trên mobile) -->
    <button id="sidebar-toggle"
        class="lg:hidden w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md mb-4 flex items-center justify-between">
        <span>Menu cá nhân</span>
        <i class="fas fa-bars"></i>
    </button>

    <!-- Menu hồ sơ -->
    <div class="space-y-1">
        <a href="#posts"
            class="tab-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
            data-tab="posts">
            <i class="fas fa-newspaper mr-3 text-lg text-blue-500"></i>
            <span>Bài viết đã đăng</span>
        </a>
        <a href="#personal-info"
            class="tab-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
            data-tab="personal-info">
            <i class="fas fa-user-circle mr-3 text-lg text-blue-500"></i>
            <span>Thông tin cá nhân</span>
        </a>
        <a href="#books"
            class="tab-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
            data-tab="books">
            <i class="fas fa-book mr-3 text-lg text-blue-500"></i>
            <span>Sách đã đăng</span>
        </a>
        <a href="#likes"
            class="tab-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
            data-tab="likes">
            <i class="fas fa-heart mr-3 text-lg text-blue-500"></i>
            <span>Đã thích</span>
        </a>
    </div>

    <!-- Thống kê hồ sơ -->
    <div class="mt-6 pt-4 border-t border-gray-200">
        <h3 class="font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-chart-line mr-2 text-blue-500"></i>
            Thống kê
        </h3>

        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Bài viết:</span>
                <span class="font-medium">{{ $postCount ?? 0 }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Sách:</span>
                <span class="font-medium">{{ $bookCount ?? 0 }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Lượt thích:</span>
                <span class="font-medium">{{ $likeCount ?? 0 }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Bình luận:</span>
                <span class="font-medium">{{ $commentCount ?? 0 }}</span>
            </div>
        </div>

        <!-- Tổng lượt xem -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Tổng lượt xem</h4>
            <div class="flex justify-between items-center">
                <div class="text-center flex-1 bg-blue-50 rounded-lg p-2 mr-2">
                    <span class="block text-blue-600 font-bold text-lg">{{ $totalBookViews ?? 0 }}</span>
                    <span class="text-xs text-gray-500">Sách</span>
                </div>
                <div class="text-center flex-1 bg-green-50 rounded-lg p-2">
                    <span class="block text-green-600 font-bold text-lg">{{ $totalBlogViews ?? 0 }}</span>
                    <span class="text-xs text-gray-500">Bài viết</span>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const leftSidebar = document.getElementById('left-sidebar');
            const mainContent = document.getElementById('main-content');

            if (sidebarToggle && leftSidebar && mainContent) {
                sidebarToggle.addEventListener('click', function() {
                    leftSidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }
        });
    </script>
@endpush
