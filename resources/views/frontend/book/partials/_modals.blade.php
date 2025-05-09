<!-- Create Book Modal -->
<div id="create-book-modal" class="fixed inset-0 z-50 overflow-y-auto hidden modal-container" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop" aria-hidden="true"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full modal-content">
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b modal-header">
                <h3 class="text-xl font-bold text-gray-800" id="modal-title">
                    <i class="fas fa-book-medical mr-2"></i>Đăng sách mới
                </h3>
                <button type="button" id="close-create-book-modal" class="text-gray-400 hover:text-gray-500 modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="px-6 py-4 max-h-[80vh] overflow-y-auto modal-body">
                <form id="create-book-form" action="{{ route('front.book.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên sách <span class="text-red-500">*</span></label>
                        <input type="text" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ảnh bìa</label>
                        <div class="dropzone" id="bookImageDropzone" data-url="{{ route('public.upload.avatar') }}"></div>
                        <input type="hidden" name="photo" id="uploadedBookImage">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Thông tin</label>
                        <textarea name="summary" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung</label>
                        <textarea name="content" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tài liệu đính kèm <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-all hover:border-blue-500 hover:bg-blue-50">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-file-upload text-blue-500 text-3xl mb-3"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="document-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-blue-500 focus-within:outline-none transition-colors">
                                        <span>Tải lên tài liệu</span>
                                        <input id="document-upload" name="document[]" type="file" class="sr-only" multiple required>
                                    </label>
                                    <p class="pl-1">hoặc kéo thả vào đây</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Chấp nhận các định dạng: PDF, DOCX, JPG, PNG, MP3, MP4
                                </p>
                                <div id="selected-files" class="mt-3 text-sm text-gray-500"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="book_type_id" class="block text-sm font-medium text-gray-700 mb-2">Loại sách <span class="text-red-500">*</span></label>
                            <select name="book_type_id" id="book_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary" required>
                                <option value="">Chọn loại sách</option>
                                @foreach ($booktypes as $bookType)
                                    <option value="{{ $bookType->id }}">{{ $bookType->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                            <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary" required>
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="book-tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                        <select id="book-tags" name="tag_ids[]" multiple placeholder="Chọn hoặc tạo tags..." class="form-control">
                            @php
                                $tags = \App\Models\Tag::where('status', 'active')->orderBy('title', 'ASC')->get();
                            @endphp
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Phần xác nhận bản quyền -->
                    <div class="mt-6 border-t pt-4 copyright-section">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="copyright-confirmation" name="copyright_confirmation" type="checkbox" class="focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded" required>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="copyright-confirmation" class="font-medium text-gray-700">Xác nhận bản quyền <span class="text-red-500">*</span></label>
                                <p class="text-gray-500">Tôi xác nhận rằng tôi sở hữu bản quyền hoặc có quyền đăng tải tài liệu này và không vi phạm bất kỳ quyền sở hữu trí tuệ của bên thứ ba nào.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 border-t pt-4 modal-footer">
                        <button type="button" id="cancel-create-book" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 modal-btn modal-btn-secondary">
                            <i class="fas fa-times mr-1"></i> Hủy
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-blue-600 modal-btn modal-btn-primary">
                            <i class="fas fa-upload mr-1"></i> Đăng sách
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Download Resources Modal -->
<div id="download-resources-modal" class="fixed inset-0 z-50 overflow-y-auto hidden modal-container" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop" aria-hidden="true"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full modal-content">
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b modal-header">
                <h3 class="text-xl font-bold text-gray-800" id="resources-modal-title">Tài liệu đính kèm</h3>
                <button type="button" id="close-download-resources-modal" class="text-gray-400 hover:text-gray-500 modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="px-6 py-4 modal-body">
                <div id="resources-list" class="mb-4">
                    <div class="text-center py-8">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Đang tải...</span>
                        </div>
                        <p class="mt-2 text-gray-600">Đang tải danh sách tài liệu...</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3 border-t pt-4 modal-footer">
                    <button type="button" id="cancel-download-resources" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 modal-btn modal-btn-secondary">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scroll to Top Button -->
<button id="scroll-to-top"
    class="fixed bottom-6 right-6 bg-primary text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center opacity-0 invisible transition">
    <i class="fas fa-arrow-up"></i>
</button> 