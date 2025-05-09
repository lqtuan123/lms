@extends('frontend.layouts.master')
@section('css')
    <link rel="stylesheet" href="{{ asset('frontend/css/book/book.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Styles bổ sung để đảm bảo hoạt động của modal và dropzone */
        .dropzone {
            border: 2px dashed #4f46e5;
            border-radius: 0.375rem;
            padding: 1.5rem;
            text-align: center;
            background: #F9FAFB;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dropzone:hover {
            background: #EFF6FF;
            border-color: #4338ca;
        }

        .dropzone .dz-message {
            margin: 1.5rem 0;
            font-size: 0.875rem;
            color: #6B7280;
        }

        .dropzone .dz-preview {
            margin: 0.5rem;
        }

        .dropzone .dz-preview .dz-image {
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .dropzone .dz-preview .dz-remove {
            color: #EF4444;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .dropzone .dz-preview .dz-remove:hover {
            text-decoration: underline;
            color: #DC2626;
        }

        /* Tom Select styling */
        .ts-wrapper {
            border-radius: 0.375rem;
        }

        .ts-control {
            border-color: #D1D5DB !important;
            padding: 0.5rem !important;
            border-radius: 0.375rem !important;
            box-shadow: none !important;
            transition: all 0.2s ease-in-out !important;
        }

        .ts-control:focus {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2) !important;
        }

        .ts-dropdown {
            border-radius: 0.375rem !important;
            border-color: #D1D5DB !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            animation: fadeIn 0.2s ease-in-out !important;
        }

        .ts-dropdown .option.active {
            background-color: #EFF6FF !important;
            color: #1E40AF !important;
        }

        .ts-dropdown .option:hover {
            background-color: #F3F4F6 !important;
        }

        .ts-dropdown .create {
            color: #4f46e5 !important;
        }

        .ts-wrapper.multi .ts-control > div {
            background-color: #EFF6FF !important;
            color: #1E40AF !important;
            border-radius: 9999px !important;
            margin: 0.125rem !important;
            padding: 0.125rem 0.5rem !important;
            border: none !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }

        .ts-wrapper.multi .ts-control > div .remove {
            border-left: none !important;
            padding-left: 0.25rem !important;
            color: #1E40AF !important;
        }

        .ts-wrapper.plugin-remove_button .item .remove {
            border-left: none !important;
            padding-left: 0.25rem !important;
            color: #1E40AF !important;
        }

        /* Modal styling fixes */
        #create-book-modal.hidden,
        #download-resources-modal.hidden,
        #quick-view-modal:not(.active) {
            display: none !important;
        }

        #quick-view-modal.active {
            display: flex !important;
        }

        #modal-loading.hidden {
            display: none !important;
        }
        
        /* Fix khẩn cấp cho modal */
        body.modal-open {
            overflow: hidden;
        }
        
        /* Đảm bảo modal-content nằm trên cùng */
        .modal-content {
            z-index: 10000 !important;
            position: relative !important;
            transform: translateY(0);
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            opacity: 1;
        }
        
        /* Hiệu ứng xuất hiện cho modal */
        .modal-container:not(.hidden) .modal-content {
            animation: slideInUp 0.3s ease-out forwards;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Hiệu ứng mờ dần cho modal backdrop */
        .modal-backdrop {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal-container:not(.hidden) .modal-backdrop {
            opacity: 0.75;
        }
        
        /* Làm đẹp các input và button trong modal */
        .modal-body input[type="text"],
        .modal-body input[type="number"],
        .modal-body input[type="email"],
        .modal-body input[type="password"],
        .modal-body textarea,
        .modal-body select {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #D1D5DB;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .modal-body input:focus,
        .modal-body textarea:focus,
        .modal-body select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        
        /* Cải thiện header của modal */
        .modal-header {
            background: linear-gradient(135deg, #4f46e5 0%, #3b30c0 100%);
            color: white;
            border-bottom: none;
        }
        
        .modal-header h3 {
            color: white !important;
            font-weight: 600;
        }
        
        .modal-close {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.2s ease;
        }
        
        .modal-close:hover {
            color: white !important;
            transform: rotate(90deg);
        }
        
        /* Cải thiện các nút trong modal */
        .modal-btn {
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .modal-btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #3b30c0 100%) !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .modal-btn-primary:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: linear-gradient(135deg, #4338ca 0%, #312894 100%) !important;
            transform: translateY(-1px);
        }
        
        .modal-btn-secondary:hover {
            background-color: #F3F4F6;
            transform: translateY(-1px);
        }
        
        /* Đảm bảo các controls trong modal có thể click được */
        #create-book-modal button,
        #create-book-modal input,
        #create-book-modal select,
        #create-book-modal textarea,
        #create-book-modal .dropzone {
            z-index: 10001 !important;
            position: relative !important;
            pointer-events: auto !important;
        }
        
        /* Đảm bảo dropzone nhận được sự kiện click */
        #bookImageDropzone {
            position: relative;
            z-index: 10001;
            cursor: pointer;
        }
        
        #bookImageDropzone * {
            pointer-events: auto;
        }
        
        /* Fix cho input file */
        #document-upload {
            position: absolute;
            height: 1px;
            width: 1px;
            opacity: 0;
        }
        
        /* Cập nhật style cho bộ lọc sách */
        .category-filter {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .category-filter:hover {
            background-color: #4f46e5 !important;
            color: white !important;
        }
        
        .category-filter.active {
            background-color: #4f46e5 !important;
            color: white !important;
            border-color: #4338ca !important;
        }
        
        /* Loại bỏ highlight cho tác giả */
        #modal-book-author {
            color: #6B7280 !important;
            font-weight: normal !important;
        }
        
        /* Styles cho phần xác nhận bản quyền */
        #copyright-confirmation {
            cursor: pointer;
            width: 1.25rem;
            height: 1.25rem;
        }
        
        #copyright-confirmation:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
        }
        
        #copyright-confirmation:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        
        label[for="copyright-confirmation"] {
            cursor: pointer;
            user-select: none;
        }
        
        /* Phần xác nhận bản quyền */
        .copyright-section {
            background-color: #F9FAFB;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-top: 1.5rem;
            border: 1px solid #E5E7EB;
            transition: all 0.2s ease;
        }
        
        .copyright-section:hover {
            background-color: #F3F4F6;
            border-color: #D1D5DB;
        }
    </style>
@endsection

@section('content')
   
    <main class="mx-auto py-6">
        <div class="flex flex-col lg:flex-row">
            <!-- Left Sidebar -->
            @include('frontend.book.partials._left_sidebar')

            <!-- Main Content Area -->
            <div id="main-content" class="main-content lg:w-4/5 lg:px-4">
                <!-- Top Bar with Search -->
                @include('frontend.book.partials._top_filters')

                <!-- Book Grid Section -->
                @include('frontend.book.partials._book_grid')
            </div>
        </div>
    </main>

    <!-- Quick View Modal -->
    <div id="quick-view-modal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b">
                    <h3 class="text-xl font-bold text-gray-800" id="modal-book-title">Tên sách</h3>
                    <button type="button" id="close-quick-view" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="modal-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Đang tải...</span>
                    </div>
                </div>
                
                <div id="modal-content" class="px-6 py-4 max-h-[80vh] overflow-y-auto">
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="md:w-1/3 flex-shrink-0">
                            <img id="modal-book-cover" src="" alt="Book Cover" class="w-full h-auto rounded-lg shadow">
                        </div>
                        <div class="md:w-2/3">
                            <div class="mb-4">
                                <p id="modal-book-author" class="text-gray-600">Tác giả</p>
                                <div class="flex items-center mt-1">
                                    <div id="modal-book-stars" class="flex text-yellow-400"></div>
                                    <span id="modal-book-rating" class="ml-2 text-sm text-gray-500"></span>
                                    <span id="modal-book-views" class="ml-4 text-sm text-gray-500"></span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div id="modal-book-tags" class="flex flex-wrap gap-2"></div>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-700 mb-2">Thông tin</h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div id="modal-book-publisher-container" class="hidden">
                                        <span class="text-gray-500">Nhà xuất bản:</span>
                                        <span id="modal-book-publisher" class="font-medium text-gray-800"></span>
                                    </div>
                                    <div id="modal-book-year-container" class="hidden">
                                        <span class="text-gray-500">Năm xuất bản:</span>
                                        <span id="modal-book-year" class="font-medium text-gray-800"></span>
                                    </div>
                                    <div id="modal-book-pages-container" class="hidden">
                                        <span class="text-gray-500">Số trang:</span>
                                        <span id="modal-book-pages" class="font-medium text-gray-800"></span>
                                    </div>
                                    <div id="modal-book-language-container" class="hidden">
                                        <span class="text-gray-500">Ngôn ngữ:</span>
                                        <span id="modal-book-language" class="font-medium text-gray-800"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-700 mb-2">Mô tả</h4>
                                <p id="modal-book-summary" class="text-gray-600 text-sm">Đang tải...</p>
                            </div>
                            
                            <div id="modal-book-action-buttons" class="flex flex-wrap gap-2 mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('frontend.book.partials._modals')
    
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script>
        // Đảm bảo Dropzone.autoDiscover = false trước khi bất kỳ script nào chạy
        if (typeof Dropzone !== 'undefined') {
            console.log('Đặt Dropzone.autoDiscover = false ngay từ đầu');
            Dropzone.autoDiscover = false;
        } else {
            console.error('Thư viện Dropzone chưa được tải');
        }
    </script>
    <script src="{{ asset('frontend/js/book/_book_scripts.js') }}"></script>
    <script>
        // Kiểm tra lỗi và debug modal
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, kiểm tra tương tác dropzone...');
            
            // Biến kiểm soát việc khởi tạo Dropzone
            let isDropzoneInitialized = false;
            
            // Cố định vấn đề click vào Dropzone
            function fixDropzoneInteraction() {
                const modalDropzone = document.getElementById('bookImageDropzone');
                if (!modalDropzone) {
                    console.error('Không tìm thấy phần tử #bookImageDropzone');
                    return;
                }
                
                console.log('Tìm thấy phần tử Dropzone');
                
                // Đảm bảo Dropzone có thể click được
                modalDropzone.style.position = 'relative';
                modalDropzone.style.zIndex = '100';
                modalDropzone.style.pointerEvents = 'auto';
                
                // Kiểm tra xem dropzone đã được khởi tạo chưa
                if (isDropzoneInitialized || modalDropzone.dropzone || window.bookImageDropzone) {
                    console.log('Dropzone đã được khởi tạo, không khởi tạo lại');
                    return;
                }
                
                // Thêm sự kiện click trực tiếp vào khu vực Dropzone
                modalDropzone.addEventListener('click', function(e) {
                    console.log('Dropzone clicked');
                    
                    // Kiểm tra xem có input file nào trong Dropzone không
                    const fileInput = modalDropzone.querySelector('input[type=file]');
                    if (fileInput) {
                        console.log('Tìm thấy input file, trigger click');
                        // Ngăn event bubbling
                        e.stopPropagation();
                        // Trigger click vào input file
                        fileInput.click();
                    } else {
                        console.log('Không tìm thấy input file trong Dropzone');
                    }
                });
                
                // Ghi nhớ rằng dropzone đã được xử lý
                isDropzoneInitialized = true;
            }
            
            // Sửa vấn đề với input file trong form
            const documentUpload = document.getElementById('document-upload');
            if (documentUpload) {
                const uploadLabel = documentUpload.closest('label');
                if (uploadLabel) {
                    uploadLabel.addEventListener('click', function(e) {
                        console.log('Upload label clicked');
                    });
                }
            }
            
            // Kiểm tra xem modal đã tồn tại chưa
            const createBookBtn = document.getElementById('create-book-btn');
            const createBookModal = document.getElementById('create-book-modal');
            
            if (createBookBtn && createBookModal) {
                // Khi nút được click, kiểm tra và khắc phục vấn đề
                createBookBtn.addEventListener('click', function() {
                    console.log('Đã click nút tạo sách');
                    
                    // Đợi modal hiển thị và kiểm tra xem có cần fix Dropzone hay không
                    setTimeout(function() {
                        if (!isDropzoneInitialized) {
                            fixDropzoneInteraction();
                        } else {
                            console.log('Dropzone đã được xử lý trước đó');
                        }
                    }, 300);
                });
            } else {
                console.log('Không tìm thấy nút hoặc modal tạo sách');
            }
        });
    </script>
@endsection
