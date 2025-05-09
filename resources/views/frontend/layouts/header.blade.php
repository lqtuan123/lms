<style>
    /* CSS để đảm bảo dropdown menu hiển thị đúng */
    .dropdown-menu {
        display: none;
        transform: translateX(-50%);
        right: -50% !important;
    }

    .modal {
        z-index: 1060 !important;
    }

    .modal-backdrop {
        z-index: 1055 !important;
    }

    .dropdown-menu.active {
        display: block;
    }

    /* Logo hiện đại với gradient và hiệu ứng */
    .logo-container {
        position: relative;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .logo-container:hover {
        transform: translateY(-2px);
    }

    .logo-icon {
        position: relative;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        border-radius: 12px;
        margin-right: 10px;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .logo-icon:before {
        content: '';
        position: absolute;
        top: -10px;
        left: -10px;
        right: -10px;
        bottom: -10px;
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        filter: blur(10px);
        opacity: 0.3;
        z-index: -1;
        transition: all 0.3s ease;
    }

    .logo-icon i {
        color: white;
        font-size: 1.2rem;
        z-index: 1;
    }

    .logo-container:hover .logo-icon {
        transform: rotate(5deg);
        box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
    }

    .logo-text {
        font-weight: 800;
        font-size: 1.5rem;
        position: relative;
    }

    .logo-text-read {
        color: #4f46e5;
        font-weight: 900;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .logo-text-social {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .logo-text span {
        position: relative;
    }

    .logo-text span:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        transition: width 0.3s ease;
    }

    .logo-container:hover .logo-text span:after {
        width: 100%;
    }

    .logo-badge {
        position: absolute;
        top: -5px;
        right: -12px;
        background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
        color: rgb(20, 18, 18);
        font-size: 0.6rem;
        font-weight: bold;
        padding: 1px 5px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
        transform: scale(0);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .logo-container:hover .logo-badge {
        transform: scale(1);
    }

    /* Thiết kế gọn gàng cho dropdown thông báo */
    .notification-item {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    .notification-item p {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 300px;
    }

    .notification-item .text-xs {
        font-size: 0.65rem;
    }

    /* Hiệu ứng khi hover vào avatar */
    .avatar-container:hover .avatar-ring {
        border-color: #4f46e5;
        transform: scale(1.05);
    }

    .avatar-container:hover .avatar {
        transform: scale(1.1);
    }

    /* Hiệu ứng đặc biệt cho avatar guest */
    #guest-avatar-button:hover .avatar-ring {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }

    #guest-avatar-button:hover .avatar {
        transform: scale(1.1);
    }

    .avatar,
    .avatar-ring {
        transition: all 0.2s ease;
    }

    /* Hiệu ứng cho dropdown menu */
    @keyframes dropdown-appear {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu.active {
        animation: dropdown-appear 0.2s ease forwards;
    }

    /* Overlay để đóng menu - phải nằm dưới menu */
    .mobile-menu-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 40;
        display: none;
    }
    
    .mobile-menu-overlay.active {
        display: block;
    }

    /* CSS cho Mobile Menu */
    .mobile-menu {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background-color: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 50;
        display: none;
        flex-direction: column;
        padding: 1rem;
        max-height: calc(100vh - 70px);
        overflow-y: auto;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    
    /* Khi menu hiển thị */
    .mobile-menu.active {
        display: flex;
        opacity: 1;
        pointer-events: auto;
        animation: none;
    }
    
    /* Thiết kế các mục trong menu */
    .mobile-menu a {
        display: block;
        padding: 0.75rem 1rem;
        margin-bottom: 0.25rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }
    
    .mobile-menu a:hover {
        background-color: #f3f4f6;
    }
    
    /* Nút đóng menu */
    .mobile-menu-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #f3f4f6;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1;
    }
</style>

<header class="bg-white shadow-sm sticky top-0 z-40">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center">
            <a href="{{ route('home') }}" class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="logo-text">
                    <span class="logo-text-read">Read</span><span class="logo-text-social">Social</span>
                    <div class="logo-badge">beta</div>
                </div>
            </a>
        </div>

        <!-- Search Bar (Center) -->
        <div class="hidden md:flex mx-4 flex-1 max-w-xl">
            <form action="{{ route('front.book.search') }}" method="GET" class="w-full">
                <div
                    class="search-bar relative w-full flex items-center rounded-full border border-gray-300 bg-gray-50 px-4 py-2">
                    <i class="fas fa-search text-gray-400 mr-2"></i>
                    <input type="text" name="book_title" placeholder="Tìm kiếm sách, tài liệu, nhóm..."
                        class="bg-transparent w-full focus:outline-none">
                    <button type="submit"
                        class="ml-2 bg-blue-500 text-white px-4 py-1 rounded-full text-sm hover:bg-blue-600 transition">Tìm</button>
                </div>
            </form>
        </div>

        <!-- Navigation Menu -->
        <nav class="hidden md:flex space-x-6">
            <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 font-medium">Trang chủ</a>
            <a href="{{ route('front.tblogs.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Cộng
                đồng</a>
            <a href="{{ route('group.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Nhóm học tập</a>
            <a href="{{ route('front.book.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Thư viện</a>
            <a href="{{ route('front.leaderboard') }}" class="text-gray-700 hover:text-blue-600 font-medium">
                <i class="fas fa-trophy text-yellow-500 mr-1"></i> Vinh danh
            </a>
        </nav>

        <!-- User Area -->
        <div class="flex items-center">
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden text-gray-600 mr-4">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <!-- Search Icon for Mobile -->
            <button id="mobile-search-button" class="md:hidden text-gray-600 mr-4">
                <i class="fas fa-search text-xl"></i>
            </button>

            <!-- Notifications Icon (Only for logged in users) -->
            @auth
                <div class="dropdown relative mr-4">
                    <button id="notification-button" class="text-gray-600 hover:text-blue-600 relative p-2">
                        <i class="fas fa-bell text-xl"></i>
                        <span id="notification-count"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center {{ auth()->user()->unreadNotificationsCount() == 0 ? 'hidden' : '' }}">
                            {{ auth()->user()->unreadNotificationsCount() }}
                        </span>
                    </button>

                    <div id="notification-dropdown"
                        class="dropdown-menu absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg overflow-hidden z-50">
                        <!-- Header với nút tùy chọn -->
                        <div class="px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-bold">Thông báo</h2>
                            <div class="flex items-center space-x-2">

                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="flex border-b">
                            <button id="all-notifications-tab"
                                class="flex-1 py-2 px-4 text-center bg-blue-50 text-blue-600 font-medium border-b-2 border-blue-600 transition">
                                Tất cả
                            </button>
                            <button id="unread-notifications-tab"
                                class="flex-1 py-2 px-4 text-center text-gray-600 hover:bg-gray-50 transition">
                                Chưa đọc
                            </button>
                        </div>

                        <!-- Section header -->
                        <div class="flex justify-between items-center px-4 py-1 bg-gray-50 border-b">
                            <button id="mark-all-read-button" class="text-xs text-blue-600 hover:text-blue-800"
                                style="white-space: nowrap;">
                                Đánh dấu tất cả đã đọc
                            </button>
                            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">Xem
                                tất cả</a>
                        </div>

                        <!-- Notifications list -->
                        <div id="notification-list" class="max-h-96 overflow-y-auto">
                            <!-- Notifications will be loaded via AJAX -->
                            <div class="text-center py-4 text-gray-500 text-sm">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải...
                            </div>
                        </div>
                    </div>
                </div>
            @endauth

            <!-- User Authentication Area -->
            @auth
                <!-- User Avatar with Dropdown (Logged in) -->
                <div class="dropdown relative">
                    <button id="user-menu-button" class="avatar-container flex items-center focus:outline-none">
                        <div class="relative">
                            <div class="avatar-ring absolute inset-0 rounded-full border-2 border-blue-500"></div>
                            <img src="{{ auth()->user()->photo ?? 'https://randomuser.me/api/portraits/women/44.jpg' }}"
                                alt="User" class="avatar w-10 h-10 rounded-full object-cover shadow-sm">
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white">
                            </div>
                        </div>
                    </button>

                    <div id="user-dropdown"
                        class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('front.profile') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                            <i class="fas fa-user mr-2 text-gray-400"></i> Trang cá nhân
                        </a>
                        <a href="{{ route('user.books.index') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                            <i class="fas fa-bookmark mr-2 text-gray-400"></i> Tài liệu đã lưu
                        </a>
                        <a href="{{ route('group.index') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                            <i class="fas fa-users mr-2 text-gray-400"></i> Nhóm của tôi
                        </a>
                        <a href="{{ route('frontend.book.advanced-search') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                            <i class="fas fa-search mr-2 text-gray-400"></i> Tìm kiếm nâng cao
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            @else
                <!-- Guest Avatar with Modal Trigger (Not logged in) -->
                <div class="dropdown relative">
                    <button id="guest-avatar-button" type="button"
                        class="avatar-container flex items-center focus:outline-none">
                        <div class="relative">
                            <div class="avatar-ring absolute inset-0 rounded-full border-2 border-gray-300"></div>
                            <img src="https://cdn-icons-png.flaticon.com/512/6596/6596121.png" alt="Guest"
                                class="avatar w-10 h-10 rounded-full object-cover shadow-sm bg-gray-50">
                        </div>
                    </button>

                    <!-- Dropdown menu for guest -->
                    <div id="guest-dropdown"
                        class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-700">Chào mừng</p>
                            <p class="text-xs text-gray-500">Vui lòng đăng nhập để tiếp tục</p>
                        </div>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#loginModal"
                            id="dropdown-login-btn"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2 text-gray-400"></i> Đăng nhập
                        </button>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#loginModal"
                            id="dropdown-register-btn"
                            class="register-btn block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                            <i class="fas fa-user-plus mr-2 text-gray-400"></i> Đăng ký tài khoản
                        </button>
                    </div>

                    <!-- Invisible button for register tab trigger -->
                    <button id="register-tab-trigger" type="button" data-bs-toggle="modal" data-bs-target="#loginModal"
                        class="register-btn hidden"></button>
                </div>
            @endauth
        </div>
    </div>

    <!-- Overlay để đóng menu khi người dùng click bên ngoài -->
    <div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu">
        <button id="mobile-menu-close" class="mobile-menu-close">
            <i class="fas fa-times"></i>
        </button>
        
        <form action="{{ route('front.book.search') }}" method="GET" class="pb-2">
            <div
                class="search-bar relative w-full flex items-center rounded-full border border-gray-300 bg-gray-50 px-4 py-2 mb-4">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                <input type="text" id="mobile-search-input" name="book_title" placeholder="Tìm kiếm..."
                    class="bg-transparent w-full focus:outline-none">
                <button type="submit"
                    class="ml-2 bg-blue-500 text-white px-3 py-1 rounded-full text-sm hover:bg-blue-600 transition">Tìm</button>
            </div>
        </form>
        <a href="{{ route('home') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Trang chủ</a>
       
        <a href="{{ route('front.tblogs.index') }}"
            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Cộng đồng</a>
        <a href="{{ route('group.index') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Nhóm học
            tập</a>
        <a href="{{ route('front.book.index') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Thư
            viện</a>
        <a href="{{ route('front.leaderboard') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
            <i class="fas fa-trophy text-yellow-500 mr-1"></i> Vinh danh
        </a>
        <div class="border-t border-gray-200 my-2"></div>
        @auth
            <a href="{{ route('front.profile') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Trang cá
                nhân</a>
            <a href="{{ route('user.books.index') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Tài liệu
                đã lưu</a>
            <a href="{{ route('group.index') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Nhóm của
                tôi</a>
            <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();"
                class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Đăng xuất</a>
            <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        @else
            <div class="flex items-center px-4 py-3 mb-2">
                <img src="https://cdn-icons-png.flaticon.com/512/6596/6596121.png" alt="Guest"
                    class="w-10 h-10 rounded-full object-cover border-2 border-gray-300 mr-3">
                <div>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#loginModal" id="mobile-login-btn"
                        class="block w-full text-left font-medium text-blue-600 hover:text-blue-800">
                        Đăng nhập
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#loginModal" id="mobile-register-btn"
                        class="block w-full text-left text-sm text-gray-600 hover:text-blue-600 mt-1 register-btn">
                        Đăng ký tài khoản
                    </button>
                    <p class="text-xs text-gray-500 mt-1">Đăng nhập để truy cập tất cả tính năng</p>
                </div>
            </div>
        @endauth
    </div>
</header>

<!-- Import Login Modal -->
@include('frontend.auth.login')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu elements
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileSearchButton = document.getElementById('mobile-search-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileSearchInput = document.getElementById('mobile-search-input');
        
        // Function to open mobile menu
        function openMobileMenu() {
            if (mobileMenu && mobileMenuOverlay) {
                document.body.style.overflow = 'hidden'; // Prevent scrolling
                mobileMenuOverlay.classList.add('active');
                
                // Slight delay to ensure overlay is visible first
                setTimeout(() => {
                    mobileMenu.classList.add('active');
                }, 10);
            }
        }
        
        // Function to close mobile menu
        function closeMobileMenu() {
            if (mobileMenu && mobileMenuOverlay) {
                mobileMenu.classList.remove('active');
                
                // Slight delay before hiding overlay
                setTimeout(() => {
                    mobileMenuOverlay.classList.remove('active');
                    document.body.style.overflow = ''; // Restore scrolling
                }, 200);
            }
        }
        
        // Toggle mobile menu
        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openMobileMenu();
            });
        }
        
        // Mobile search button
        if (mobileSearchButton) {
            mobileSearchButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openMobileMenu();
                
                // Focus on search input after menu opens
                setTimeout(() => {
                    if (mobileSearchInput) {
                        mobileSearchInput.focus();
                    }
                }, 300);
            });
        }
        
        // Close button for mobile menu
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeMobileMenu();
            });
        }
        
        // Close menu when clicking overlay
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', function() {
                closeMobileMenu();
            });
        }
        
        // Close menu when clicking on links
        const mobileMenuLinks = mobileMenu ? mobileMenu.querySelectorAll('a:not([href="#"])') : [];
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Close menu if link is not in search form
                if (!this.closest('form')) {
                    // Prevent default only temporarily to ensure menu closes first
                    e.preventDefault();
                    closeMobileMenu();
                    
                    // After menu closes, follow the link
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 300);
                }
            });
        });
        
        // Close menu with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Toggle user dropdown
        const userMenuButton = document.getElementById('user-menu-button');
        if (userMenuButton) {
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation(); // Ngăn chặn sự kiện click lan ra document
                const dropdown = document.getElementById('user-dropdown');
                dropdown.classList.toggle('active');
            });
        }

        // Toggle guest dropdown 
        const guestAvatarButton = document.getElementById('guest-avatar-button');
        if (guestAvatarButton) {
            guestAvatarButton.addEventListener('click', function(e) {
                e.stopPropagation(); // Ngăn chặn sự kiện click lan ra document
                const dropdown = document.getElementById('guest-dropdown');
                dropdown.classList.toggle('active');
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            // User dropdown
            const dropdown = document.getElementById('user-dropdown');
            const userMenuButton = document.getElementById('user-menu-button');

            if (dropdown && dropdown.classList.contains('active') &&
                userMenuButton && !userMenuButton.contains(event.target) &&
                !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }

            // Guest dropdown
            const guestDropdown = document.getElementById('guest-dropdown');
            const guestAvatarButton = document.getElementById('guest-avatar-button');

            if (guestDropdown && guestDropdown.classList.contains('active') &&
                guestAvatarButton && !guestAvatarButton.contains(event.target) &&
                !guestDropdown.contains(event.target)) {
                guestDropdown.classList.remove('active');
            }
        });

        // Xử lý hiển thị tab đăng ký khi nhấn nút đăng ký
        const registerButtons = document.querySelectorAll('.register-btn');
        if (registerButtons.length > 0) {
            registerButtons.forEach(button => {
                button.addEventListener('click', function() {
                    localStorage.setItem('openRegisterTab', 'true');
                });
            });
        }

        // Thêm nút gọi tới tab đăng ký
        const mobileRegisterBtn = document.getElementById('mobile-register-btn');
        if (mobileRegisterBtn) {
            mobileRegisterBtn.addEventListener('click', function() {
                localStorage.setItem('openRegisterTab', 'true');
            });
        }

        // Xử lý đăng nhập từ dropdown
        const dropdownLoginBtn = document.getElementById('dropdown-login-btn');
        if (dropdownLoginBtn) {
            dropdownLoginBtn.addEventListener('click', function() {
                localStorage.removeItem('openRegisterTab');
            });
        }

        // Xử lý đăng ký từ dropdown
        const dropdownRegisterBtn = document.getElementById('dropdown-register-btn');
        if (dropdownRegisterBtn) {
            dropdownRegisterBtn.addEventListener('click', function() {
                localStorage.setItem('openRegisterTab', 'true');
            });
        }

        // Toggle notification dropdown
        const notificationButton = document.getElementById('notification-button');
        if (notificationButton) {
            notificationButton.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = document.getElementById('notification-dropdown');
                dropdown.classList.toggle('active');

                // Nếu dropdown đang hiển thị, tải dữ liệu thông báo
                if (dropdown.classList.contains('active')) {
                    loadNotifications('all');
                }
            });
        }

        // Đóng dropdown khi click bên ngoài
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notification-dropdown');
            const notificationButton = document.getElementById('notification-button');

            if (dropdown && dropdown.classList.contains('active') &&
                notificationButton && !notificationButton.contains(event.target) &&
                !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Xử lý các tab thông báo
        const allNotificationsTab = document.getElementById('all-notifications-tab');
        const unreadNotificationsTab = document.getElementById('unread-notifications-tab');

        if (allNotificationsTab && unreadNotificationsTab) {
            // Tab Tất cả
            allNotificationsTab.addEventListener('click', function() {
                allNotificationsTab.classList.add('bg-blue-50', 'text-blue-600', 'border-b-2',
                    'border-blue-600');
                unreadNotificationsTab.classList.remove('bg-blue-50', 'text-blue-600', 'border-b-2',
                    'border-blue-600');
                unreadNotificationsTab.classList.add('text-gray-600');

                loadNotifications('all');
            });

            // Tab Chưa đọc
            unreadNotificationsTab.addEventListener('click', function() {
                unreadNotificationsTab.classList.add('bg-blue-50', 'text-blue-600', 'border-b-2',
                    'border-blue-600');
                allNotificationsTab.classList.remove('bg-blue-50', 'text-blue-600', 'border-b-2',
                    'border-blue-600');
                allNotificationsTab.classList.add('text-gray-600');

                loadNotifications('unread');
            });
        }

        // Hàm tải thông báo
        function loadNotifications(type = 'all') {
            const notificationList = document.getElementById('notification-list');
            
            // Hiển thị loading
            notificationList.innerHTML =
                '<div class="text-center py-4 text-gray-500 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i> Đang tải...</div>';
            
            // URL tải thông báo
            let url = '/notices/get-notice?limit=10';
            if (type === 'unread') {
                url += '&filter=unread';
            }
            
            // Tải dữ liệu thông báo
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.text())
            .then(html => {
                notificationList.innerHTML = html;
                
                // Thêm event listener cho các thông báo
                const notificationItems = notificationList.querySelectorAll('.notification-item');
                notificationItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const notificationId = this.dataset.id;
                        markAsRead(notificationId);
                    });
                });
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationList.innerHTML =
                    '<div class="text-center py-4 text-red-500 text-sm">Có lỗi xảy ra khi tải thông báo.</div>';
            });
        }

        // Hàm đánh dấu thông báo đã đọc
        function markAsRead(notificationId) {
            fetch(`/notices/mark-as-read/${notificationId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật số lượng thông báo
                        updateNotificationCount();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Hàm cập nhật số lượng thông báo
        function updateNotificationCount() {
            fetch('/notices/count-unread', {
                    headers: {
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const notificationCount = document.getElementById('notification-count');
                    if (notificationCount) {
                        if (data.count > 0) {
                            notificationCount.textContent = data.count;
                            notificationCount.classList.remove('hidden');
                        } else {
                            notificationCount.textContent = '0';
                            notificationCount.classList.add('hidden');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Cập nhật số lượng thông báo mỗi 30 giây
        setInterval(updateNotificationCount, 30000);

        // Xử lý sự kiện nút đánh dấu tất cả đã đọc
        const markAllReadButton = document.getElementById('mark-all-read-button');
        if (markAllReadButton) {
            markAllReadButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                markAllNotificationsAsRead();
            });
        }

        // Hàm đánh dấu tất cả thông báo đã đọc
        function markAllNotificationsAsRead() {
            fetch('/notices/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật lại giao diện
                        document.getElementById('notification-count').classList.add('hidden');

                        // Nếu đang ở tab "Chưa đọc", chuyển sang tab "Tất cả"
                        if (document.getElementById('unread-notifications-tab').classList.contains(
                                'border-blue-600')) {
                            document.getElementById('all-notifications-tab').click();
                        } else {
                            // Nếu đang ở tab "Tất cả", load lại thông báo
                            loadNotifications('all');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                });
        }
    });
</script>
