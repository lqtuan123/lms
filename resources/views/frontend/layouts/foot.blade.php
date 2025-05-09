<!-- Bootstrap 5 JS -->
<!-- Đã thêm trong head.blade.php -->

<!-- Custom scripts -->
<script>
    // Ensure Bootstrap modal works correctly
    document.addEventListener('DOMContentLoaded', function() {
        // Fix for any z-index issues with Bootstrap modal
        const modalBackdrops = document.querySelectorAll('.modal-backdrop');
        modalBackdrops.forEach(backdrop => {
            backdrop.style.zIndex = 1040;
        });
        
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.zIndex = 1050;
        });
    });
</script>
