

@if (!$user)
  <div class="container mx-auto mt-10 px-4">
    <div class="max-w-md mx-auto bg-gradient-to-r from-indigo-500 to-indigo-700 shadow-xl rounded-lg overflow-hidden">
      <div class="p-8 text-center text-white">
        <p class="text-lg font-medium mb-6">Hãy điền thông tin email và mật khẩu để đăng nhập hệ thống.</p>
        
        <form method="POST" action="{{ route('front.login') }}" class="space-y-6">
          @csrf
          
          <div class="relative">
            <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all" placeholder="Email" id="loginEmail">
            <label for="loginEmail" class="absolute top-0 left-0 px-4 py-3 text-gray-200">Email</label>
          </div>
          
          <div class="relative">
            <input type="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all" placeholder="Mật khẩu" id="loginPassword">
            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-gray-200"><i class="uil uil-eye"></i></span>
            <label for="loginPassword" class="absolute top-0 left-0 px-4 py-3 text-gray-200">Mật khẩu</label>
          </div>
          
          <input type="hidden" name="plink" value="{{ isset($plink) ? $plink : '' }}">
          
          <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all transform hover:scale-105">Đăng nhập</button>
        </form>
        
        <div class="mt-4">
          @if (Route::has('password.request'))
            <a class="text-indigo-200 hover:text-indigo-100" href="{{ route('password.request') }}">Quên mật khẩu?</a>
          @endif
        </div>
        
        <p class="mt-2 text-gray-200">Bạn chưa có tài khoản? <a href="{{ route('front.register') }}" class="text-indigo-200 hover:text-indigo-100">Đăng ký</a></p>
        
        <div class="my-6">
          <div class="border-t border-gray-300 my-4"></div>
          <div class="flex justify-center space-x-4">
            <a href="#" class="w-10 h-10 bg-red-600 text-white flex items-center justify-center rounded-full shadow-md hover:shadow-xl transition-all"><i class="uil uil-google"></i></a>
            <a href="#" class="w-10 h-10 bg-blue-600 text-white flex items-center justify-center rounded-full shadow-md hover:shadow-xl transition-all"><i class="uil uil-facebook-f"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif
