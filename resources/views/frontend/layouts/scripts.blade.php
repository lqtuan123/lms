<!-- JavaScript Files -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
    });
    
    // Tab Switching
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'text-indigo-600', 'border-indigo-600');
                btn.classList.add('text-gray-500', 'hover:text-gray-700');
            });
            
            // Add active class to clicked button
            this.classList.add('active', 'text-indigo-600', 'border-indigo-600');
            this.classList.remove('text-gray-500', 'hover:text-gray-700');
            
            // Here you would typically show/hide the corresponding tab content
            // For this demo, we're just handling the button states
        });
    });
    
    // Book Card Interaction
    const bookCards = document.querySelectorAll('.book-card');
    
    bookCards.forEach(card => {
        card.addEventListener('click', function() {
            // In a real app, this would navigate to the book detail page
            console.log('Navigating to book detail page');
        });
    });
    
    // Community Card Interaction
    const communityCards = document.querySelectorAll('.community-card');
    
    communityCards.forEach(card => {
        card.addEventListener('click', function() {
            // In a real app, this would navigate to the discussion/group page
            console.log('Navigating to community content');
        });
    });
    
    // Simulate loading for recently read section if user is not logged in
    const isLoggedIn = false; // Change to true to see the "recently read" section
    
    if (!isLoggedIn) {
        document.getElementById('recently-read').innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Đăng nhập để xem sách đang đọc</h2>
            </div>
            <div class="bg-white rounded-lg p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mx-auto mb-4">
                    <i class="fas fa-user"></i>
                </div>
                <p class="text-gray-600 mb-4">Đăng nhập để xem danh sách sách bạn đang đọc và tiếp tục từ trang đã dừng.</p>
                <a href="#" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-full font-medium hover:bg-indigo-700">Đăng nhập ngay</a>
            </div>
        `;
    }
</script> 