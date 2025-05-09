<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="modal-header border-b border-gray-200 bg-gray-50 p-4 flex justify-between items-center">
                <h5 class="text-lg font-semibold text-gray-700">Tài khoản người dùng</h5>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" data-bs-dismiss="modal" aria-label="Close">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body p-6">
                <div class="tabs-listing">
                    <!-- Tab headers -->
                    <nav class="mb-6">
                        <div class="flex rounded-md bg-gray-100 p-1 text-sm" role="tablist">
                            <button class="w-full py-2.5 px-3 rounded-md font-medium transition ease-in-out duration-200 text-center focus:outline-none tab-active bg-white shadow" id="tab-login" data-tab="login-tab" type="button" role="tab">
                                Đăng nhập
                            </button>
                            <button class="w-full py-2.5 px-3 rounded-md font-medium transition ease-in-out duration-200 text-center text-gray-600 hover:text-gray-900 focus:outline-none" id="tab-register" data-tab="register-tab" type="button" role="tab">
                                Đăng ký
                            </button>
                        </div>
                    </nav>
                    
                    <!-- Tab contents -->
                    <div class="tab-content">
                        <!-- Login Tab -->
                        <div id="login-tab" class="tab-pane active">
                            <form method="POST" action="{{ route('front.login.submit') }}" class="space-y-4">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                            </svg>
                                        </div>
                                        <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror" 
                                            placeholder="Nhập email của bạn" required>
                                    </div>
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <div class="flex items-center justify-between mb-1">
                                        <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                                        <a href="{{ route('front.password.request') }}" class="text-xs text-blue-600 hover:text-blue-800">
                                            Quên mật khẩu?
                                        </a>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 116 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <input type="password" name="password" id="password" 
                                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                            placeholder="Nhập mật khẩu" required>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                                        Ghi nhớ đăng nhập
                                    </label>
                                </div>
                                
                                <input type="hidden" name="plink" value="{{ url()->full() }}">
                                
                                <button type="submit"
                                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    Đăng nhập
                                </button>
                                
                                <div class="mt-4">
                                    <p class="text-center text-sm text-gray-600">Hoặc đăng nhập bằng</p>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <a href="{{ route('auth.socialite.redirect', 'google') }}" class="flex items-center justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                        <svg class="h-5 w-5 mr-2" fill="#4285F4" viewBox="0 0 24 24">
                                            <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"/>
                                        </svg>
                                        <span>Google</span>
                                    </a>
                                    <a href="{{ route('auth.socialite.redirect', 'facebook') }}" class="flex items-center justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                        <svg class="h-5 w-5 mr-2" fill="#1877F2" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        <span>Facebook</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Register Tab -->
                        <div id="register-tab" class="tab-pane hidden">
                            @include('frontend.auth.register')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <!-- Thêm link quên mật khẩu phía dưới modal -->
<div class="text-center mt-4">
    <p class="text-sm text-gray-600">
        Bạn quên mật khẩu? 
        <a href="{{ route('front.password.request') }}" class="text-blue-600 hover:text-blue-800 font-medium">
            Nhấn vào đây để đặt lại mật khẩu
        </a>
    </p>
</div> --}}

<style>
    .tab-active {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    
    .tab-pane {
        transition: all 0.2s ease-in-out;
    }
    
    .tab-pane.hidden {
        display: none;
    }
    
    /* Đảm bảo modal hiển thị đúng */
    .modal {
        z-index: 1050;
    }
    
    .modal-dialog {
        max-width: 480px;
        margin: 1.75rem auto;
    }
    
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý chuyển đổi tab
        const tabButtons = document.querySelectorAll('[data-tab]');
        const tabContents = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Xóa active state từ tất cả buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('tab-active', 'bg-white', 'shadow');
                    btn.classList.add('text-gray-600', 'hover:text-gray-900');
                });
                
                // Thêm active state vào button được click
                button.classList.add('tab-active', 'bg-white', 'shadow');
                button.classList.remove('text-gray-600', 'hover:text-gray-900');
                
                // Ẩn tất cả tab content
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                });
                
                // Hiển thị tab content tương ứng
                const tabId = button.getAttribute('data-tab');
                const activeTab = document.getElementById(tabId);
                activeTab.classList.remove('hidden');
                activeTab.classList.add('active');
            });
        });
        
        // Xử lý hiển thị modal khi được trigger bởi nút đăng ký
        const loginModal = document.getElementById('loginModal');
        if (loginModal) {
            loginModal.addEventListener('shown.bs.modal', function() {
                const registerBtn = localStorage.getItem('openRegisterTab');
                if (registerBtn === 'true') {
                    const tabRegister = document.getElementById('tab-register');
                    if (tabRegister) {
                        tabRegister.click();
                    }
                    localStorage.removeItem('openRegisterTab');
                }
            });
        }
    });
</script>
