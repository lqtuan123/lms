<section class="relative overflow-hidden bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 text-white py-12">
    <!-- Hình nền động -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-16 -left-16 w-60 h-60 rounded-full bg-white animate-float-slow"></div>
        <div class="absolute bottom-12 right-12 w-44 h-44 rounded-full bg-indigo-300 animate-float"></div>
    </div>

    <!-- Nội dung chính -->
    <div
        class="container mx-auto px-4 relative z-10 max-w-screen-xl flex flex-col md:flex-row items-center justify-between gap-12">
        <!-- Bên trái: nội dung -->
        <div class="md:w-1/2 text-center md:text-left flex flex-col items-center md:items-start pl-12 md:pl-16">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">
                Học tập <span class="text-yellow-300 underline underline-offset-4">không giới hạn</span>
            </h1>
            <p class="text-base md:text-lg mb-6 text-blue-50 font-light leading-relaxed max-w-lg">
                Cùng nhau chia sẻ tri thức và phát triển bản thân mỗi ngày trong không gian học tập hiện đại và sáng
                tạo.
            </p>
            <div class="flex flex-col sm:flex-row justify-center md:justify-start gap-4 pl-12 md:pl-16">
                <a href="{{ route('front.book.index') }}"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-full bg-white text-indigo-600 font-semibold hover:bg-blue-50 shadow transition duration-300">
                    Đọc sách ngay <i class="fas fa-book ml-2"></i>
                </a>
                <a href="{{ route('front.tblogs.index') }}"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-full border border-white/50 text-white hover:bg-white/10 transition duration-300">
                    Cộng đồng <i class="fas fa-users ml-2"></i>
                </a>
            </div>
        </div>


        <!-- Bên phải: hình ảnh -->
        <div class="md:w-1/2 relative flex justify-center pl-8 md:pl-12">
            <div class="relative w-full max-w-sm">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
                    alt="People studying together" class="rounded-2xl shadow-lg w-full h-auto object-cover">

                <!-- Badge sáng tạo -->
                <div
                    class="absolute top-4 left-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow">
                    <i class="fas fa-star mr-1"></i> Sáng tạo
                </div>

                <!-- Badge học tập 4.0 -->
                <div
                    class="absolute bottom-4 right-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs px-3 py-1 rounded-full font-semibold shadow">
                    <i class="fas fa-graduation-cap mr-1"></i> Học tập 4.0
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .animate-float {
        animation: float 8s ease-in-out infinite;
    }

    .animate-float-slow {
        animation: float 10s ease-in-out infinite 2s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-12px);
        }
    }
</style>
