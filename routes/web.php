<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UGroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CFunctionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\BookFrontendController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Frontend\FilesFrontendController;
use App\Http\Controllers\Frontend\GroupFrontendController;
use Illuminate\Http\Request;

use App\Models\Comment;
use App\Modules\Book\Models\Book;
use App\Modules\Tuongtac\Controllers\TRecommendController;
use App\Modules\Tuongtac\Controllers\TCommentController;
use App\Modules\Tuongtac\Controllers\TMotionController;
use App\Modules\Tuongtac\Controllers\TuongtacController;

// Authentication Routes
Route::get('/admin/login', [LoginController::class, 'viewlogin'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.checklogin');
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Admin Routes
Route::group(['prefix' => 'admin', 'middleware' => 'admin.auth', 'as' => 'admin.'], function () {
    Route::get('/', [AdminController::class, 'index'])->name('home');
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Points Management
    Route::resource('points', App\Http\Controllers\Backend\PointController::class);
    Route::get('user/{userId}/points', [App\Http\Controllers\Backend\PointController::class, 'userHistory'])->name('user.points');
    Route::post('user/{userId}/recalculate-points', [App\Http\Controllers\Backend\PointController::class, 'recalculateUserPoints'])->name('user.recalculate-points');
    Route::post('points/{pointHistoryId}/cancel', [App\Http\Controllers\Backend\PointController::class, 'cancelPointTransaction'])->name('points.cancel');
    Route::get('points-reports', [App\Http\Controllers\Backend\PointController::class, 'reports'])->name('points.reports');
    Route::post('add-manual-points', [App\Http\Controllers\Backend\PointController::class, 'addManualPoints'])->name('points.add-manual');

    // User Management
    Route::resource('user', UserController::class);
    Route::post('user/status', [UserController::class, 'userStatus'])->name('user.status');
    Route::get('user/search', [UserController::class, 'userSearch'])->name('user.search');
    Route::get('user/sort', [UserController::class, 'userSort'])->name('user.sort');
    Route::post('user/detail', [UserController::class, 'userDetail'])->name('user.detail');
    Route::post('user/profile', [UserController::class, 'userUpdateProfile'])->name('user.profile.update');
    Route::get('user/profile', [UserController::class, 'userViewProfile'])->name('user.profile.view');
    Route::get('user/jsearch', [UserController::class, 'userJsearch'])->name('user.jsearch');
    Route::get('user/{id}/reset-password', [App\Http\Controllers\UserController::class, 'resetUserPassword'])->name('user.reset-password');
    Route::post('user/{id}/reset-password', [App\Http\Controllers\UserController::class, 'processResetPassword'])->name('user.process-reset-password');
    Route::get('user/{id}/restore', [App\Http\Controllers\UserController::class, 'restoreUser'])->name('user.restore');

    // Role Management
    Route::resource('role', RoleController::class);
    Route::post('role/status', [RoleController::class, 'roleStatus'])->name('role.status');
    Route::get('role/search', [RoleController::class, 'roleSearch'])->name('role.search');
    Route::get('role/function/{id}', [RoleController::class, 'roleFunction'])->name('role.function');
    Route::get('role/selectall/{id}', [RoleController::class, 'roleSelectall'])->name('role.selectall');
    Route::post('role/functionstatus', [RoleController::class, 'roleFucntionStatus'])->name('role.functionstatus');

    // Function Management
    Route::resource('cmdfunction', CFunctionController::class);
    Route::post('cmdfunction/status', [CFunctionController::class, 'cmdfunctionStatus'])->name('cmdfunction.status');
    Route::get('cmdfunction/search', [CFunctionController::class, 'cmdfunctionSearch'])->name('cmdfunction.search');

    // Settings
    Route::resource('setting', SettingController::class);

    // File Uploads
    Route::post('avatar-upload', [FilesController::class, 'avartarUpload'])->name('upload.avatar');
    Route::post('product-upload', [FilesController::class, 'productUpload'])->name('upload.product');
    Route::post('upload-ckeditor', [FilesController::class, 'ckeditorUpload'])->name('upload.ckeditor');
});

// Frontend Routes
Route::get('/', [IndexController::class, 'home'])->name('home');
Route::get('front/login', [IndexController::class, 'viewLogin'])->name('front.login');
Route::post('front/login/abc', [IndexController::class, 'login'])->name('front.login.submit');
Route::get('front/register', [IndexController::class, 'viewRegister'])->name('front.register');
Route::post('front/register', [IndexController::class, 'saveUser'])->name('front.register.submit');
Route::post('/front/logout', [App\Http\Controllers\Frontend\LoginController::class, 'logout'])->name('logout');
Route::post('upload-ckeditor', [\App\Http\Controllers\Frontend\FilesFrontendController::class, 'ckeditorUpload'])->name('upload.ckeditor');

// Social Authentication Routes
Route::get('auth/{provider}/redirect', [\App\Http\Controllers\Frontend\SocialiteController::class, 'redirect'])->name('auth.socialite.redirect');
Route::get('auth/{provider}/callback', [\App\Http\Controllers\Frontend\SocialiteController::class, 'callback'])->name('auth.socialite.callback');

// Public file upload routes
Route::post('/public/avatar-upload', [\App\Http\Controllers\Frontend\FilesFrontendController::class, 'avatarUpload'])->name('public.upload.avatar');

// User Profile
Route::prefix('front/profile')->group(function () {
    Route::get('/', [ProfileController::class, 'viewDashboard'])->name('front.profile');
    Route::post('/changepassword', [ProfileController::class, 'changePassword'])->name('front.profile.changepass');
    Route::get('/edit', [ProfileController::class, 'createEdit'])->name('front.profile.edit');
    Route::post('/updatedescription', [ProfileController::class, 'updateDescription'])->name('front.profile.updatedescription');
    Route::post('/updatename', [ProfileController::class, 'updateName'])->name('front.profile.updatename');
    Route::post('/update', [ProfileController::class, 'updateProfile'])->name('front.profile.update');
    Route::post('/updatefield', [ProfileController::class, 'updateField'])->name('front.profile.updatefield');
    Route::post('/privacy', [ProfileController::class, 'updatePrivacySettings'])->name('front.profile.privacy');
    Route::post('/avatar-upload', [FilesFrontendController::class, 'avatarUpload'])->name('front.upload.avatar');
    Route::post('/banner-upload', [FilesFrontendController::class, 'bannerUpload'])->name('front.upload.banner');
});

// Xem profile người dùng khác
Route::get('/user/{id}', [ProfileController::class, 'viewUserProfile'])->name('front.user.profile');

// Book Management
Route::prefix('front/book')->group(function () {
    Route::get('/', [BookFrontendController::class, 'index'])->name('front.book.index');
    Route::get('/create', [BookFrontendController::class, 'create'])->name('front.book.create');
    Route::get('/recentBook', [BookFrontendController::class, 'showRecentBooks'])->name('front.book.recentBook');
    Route::post('/store', [BookFrontendController::class, 'store'])->name('front.book.store');
    Route::get('/{id}', [BookFrontendController::class, 'show'])->name('front.book.show');
    Route::get('/type/{slug}', [BookFrontendController::class, 'booksByType'])->name('front.book.byType');
    Route::post('/bookmark', [BookFrontendController::class, 'bookMark'])->name('front.book.bookmark');
    Route::post('/vote', [BookFrontendController::class, 'vote'])->name('front.book.vote');
    Route::get('/read/{id}', [BookFrontendController::class, 'readBook'])->name('front.book.read');
});

// Mark book as read
Route::post('/books/mark-as-read', function (Request $request) {
    $bookId = $request->input('book_id');
    $book = Book::find($bookId);
    if ($book) {
        $recentlyReadBooks = session('recently_read_books', collect());
        if (!$recentlyReadBooks->contains('id', $book->id)) {
            $recentlyReadBooks->prepend($book);
            session(['recently_read_books' => $recentlyReadBooks->take(10)]);
        }
    }
    return response()->json(['success' => true]);
})->name('front.book.markAsRead');

Route::get('/books/a-search', [BookFrontendController::class, 'advancedSearch'])
    ->name('frontend.book.search');
Route::get('/books/advanced-search', [BookFrontendController::class, 'advancedSearch'])
    ->name('frontend.book.advanced-search');
Route::get('/books/search', [BookFrontendController::class, 'Search'])
    ->name('front.book.search');
Route::get('/books/search-mention', [BookFrontendController::class, 'searchForMention'])->name('books.search-mention');

// Comments
Route::post('/comments/store', [CommentController::class, 'store'])->name('comments.store');

// Group routes (Nhóm)
Route::prefix('group')->name('group.')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'destroy'])->name('destroy');
    
    // Group membership routes
    Route::post('/{id}/join', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'join'])->name('join');
    Route::post('/{id}/request-join', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'requestJoinGroup'])->name('request-join');
    Route::post('/{id}/leave', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'leaveGroup'])->name('leave');
    Route::post('/{id}/approve-member', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'approveMember'])->name('approve-member');
    Route::post('/{id}/remove-member', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'removeMember'])->name('remove-member');
    Route::post('/{id}/promote-moderator', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'promoteModerator'])->name('promote-moderator');
    Route::post('/{id}/demote-moderator', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'demoteModerator'])->name('demote-moderator');
    Route::post('/{id}/reject-member', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'rejectMember'])->name('reject-member');
    
    // API routes for JS functions
    Route::post('/{id}/promote/{user_id}', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'promoteModerator'])->name('promote');
    Route::post('/{id}/demote/{user_id}', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'demoteModerator'])->name('demote');
    Route::post('/{id}/remove/{user_id}', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'removeMember'])->name('remove');
    
    // Group post routes
    Route::post('/vote', [App\Http\Controllers\Frontend\GroupFrontendController::class, 'vote'])->name('vote');
});

use App\Http\Controllers\Frontend\UserBookController;

Route::prefix('user/books')->middleware(['auth'])->group(function () {
    Route::get('/', [UserBookController::class, 'index'])->name('user.books.index');
    Route::get('{id}/edit', [UserBookController::class, 'edit'])->name('user.books.edit');
    Route::put('{id}', [UserBookController::class, 'update'])->name('user.books.update');
    Route::delete('{id}', [UserBookController::class, 'destroy'])->name('user.books.destroy');
    Route::post('{id}/status', [UserBookController::class, 'toggleStatus'])->name('user.books.status');
    Route::delete('/resource/{resourceId}', [UserBookController::class, 'deleteResource'])
        ->name('user.books.resource.destroy');
});

Route::get('/books/filter', [BookFrontendController::class, 'booksByMultiTypes'])->name('front.book.filter');

// Contact Routes
Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');

Route::post('/contact', function (Illuminate\Http\Request $request) {
    // Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
        'phone' => 'nullable|string|max:20',
    ]);
    
    // Here you can add logic to save the contact to database
    // or send email to administrators
    
    // For now, just redirect back with success message
    return back()->with('success', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.');
})->name('contact.submit');

// Test route for book mention functionality
Route::get('/test-book-mention', function() {
    return view('frontend.test-mention');
})->name('test.book-mention');

// Thêm route cho quên mật khẩu người dùng
Route::get('/forgot-password', [App\Http\Controllers\Frontend\LoginController::class, 'showForgotPasswordForm'])->name('front.password.request');
Route::post('/forgot-password', [App\Http\Controllers\Frontend\LoginController::class, 'forgotPassword'])->name('front.password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\Frontend\LoginController::class, 'showResetPasswordForm'])->name('front.password.reset');
Route::post('/reset-password', [App\Http\Controllers\Frontend\LoginController::class, 'resetPassword'])->name('front.password.update');

// Thêm route để lấy nội dung bài viết theo slug
Route::get('/tblogs/content/{slug}', 'App\Modules\Tuongtac\Controllers\TBlogController@getPostContent')->name('front.tblogs.content');

// Thêm route để lấy dữ liệu form tạo bài viết mới
Route::get('/get-create-blog-form', 'App\Modules\Tuongtac\Controllers\TBlogController@getCreateForm')->name('front.tblogs.get-form');

// Comment Likes
Route::post('/comment-likes/toggle', 'App\Modules\Tuongtac\Controllers\CommentLikeController@toggle');

// Poll routes
Route::middleware(['auth'])->group(function () {
    Route::get('/polls', [App\Http\Controllers\PollController::class, 'index'])->name('polls.index');
    Route::get('/polls/create', [App\Http\Controllers\PollController::class, 'create'])->name('polls.create');
    Route::post('/polls', [App\Http\Controllers\PollController::class, 'store'])->name('polls.store');
    Route::get('/polls/{poll}', [App\Http\Controllers\PollController::class, 'show'])->name('polls.show');
    Route::post('/polls/{poll}/vote', [App\Http\Controllers\PollController::class, 'vote'])->name('polls.vote');
    Route::get('/polls/{poll}/edit', [App\Http\Controllers\PollController::class, 'edit'])->name('polls.edit');
    Route::put('/polls/{poll}', [App\Http\Controllers\PollController::class, 'update'])->name('polls.update');
    Route::delete('/polls/{poll}', [App\Http\Controllers\PollController::class, 'destroy'])->name('polls.destroy');
    Route::get('/polls/{poll}/voters', [App\Http\Controllers\PollController::class, 'getVoters'])->name('polls.voters');
    Route::post('/polls/{poll}/change-vote', [App\Http\Controllers\PollController::class, 'changeVote'])->name('polls.change-vote');
});

// Poll voting via AJAX
Route::post('/poll-vote/{poll}', [App\Http\Controllers\PollController::class, 'ajaxVote'])->middleware('auth')->name('polls.ajax-vote');

// Routes cho thời gian đọc sách
Route::post('/books/update-reading-time', [BookFrontendController::class, 'updateReadingTime'])->name('books.update-reading-time');
Route::post('/books/finish-reading', [BookFrontendController::class, 'finishReading'])->name('books.finish-reading');

// Thêm route mới khởi tạo phiên đọc sách
Route::post('/books/start-reading', [BookFrontendController::class, 'startReading'])->name('books.start-reading');

// Thêm route cho trang vinh danh bạn đọc
Route::get('/leaderboard', [App\Http\Controllers\Frontend\UserLeaderboardController::class, 'index'])->name('front.leaderboard');

// Routes cho đánh giá sách
Route::prefix('ratings')->group(function () {
    // Route không yêu cầu xác thực
    Route::get('/book/{bookId}', [App\Http\Controllers\RatingController::class, 'index'])->name('ratings.index');
    
    // Routes yêu cầu xác thực
    Route::middleware(['auth'])->group(function () {
        Route::post('/book/{bookId}', [App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');
        Route::put('/book/{bookId}', [App\Http\Controllers\RatingController::class, 'update'])->name('ratings.update');
        Route::delete('/{ratingId}', [App\Http\Controllers\RatingController::class, 'destroy'])->name('ratings.destroy');
        Route::get('/user/book/{bookId}', [App\Http\Controllers\RatingController::class, 'show'])->name('ratings.user');
    });
});

// API routes for dashboard
Route::prefix('admin/api')->middleware(['auth', 'admin.auth'])->group(function () {
    Route::get('/books/by/category', [App\Http\Controllers\AdminController::class, 'booksByCategory'])->name('admin.api.books.by.category');
    Route::get('/users/by/points', [App\Http\Controllers\AdminController::class, 'usersByPoints'])->name('admin.api.users.by.points');
});

// Route tải xuống tài liệu từ resource
Route::post('/resource/download', function (Illuminate\Http\Request $request) {
    $resourceId = $request->input('resource_id');
    $resource = App\Modules\Resource\Models\Resource::find($resourceId);
    
    if (!$resource) {
        return back()->with('error', 'Không tìm thấy tài liệu');
    }
    
    // Tạo link tải xuống an toàn
    $downloadLink = App\Modules\Resource\Models\Resource::createDownloadLink($resource);
    
    if (!$downloadLink) {
        return back()->with('error', 'Không thể tạo link tải xuống');
    }
    
    // Chuyển hướng đến link tải xuống
    return redirect()->to($downloadLink);
})->name('resource.download');

// Route tải xuống tài liệu trực tiếp từ ID 
Route::get('/download-resource/{id}', function ($id) {
    $resource = App\Modules\Resource\Models\Resource::find($id);
    
    if (!$resource) {
        return back()->with('error', 'Không tìm thấy tài liệu');
    }
    
    // Tạo token tải xuống
    $token = \Illuminate\Support\Str::random(32);
    
    // Lưu thông tin tải xuống vào database
    $fileDownload = App\Modules\Resource\Models\FileDownload::create([
        'file_path' => $resource->url,
        'download_token' => $token,
        'resource_id' => $resource->id,
        'is_downloaded' => false,
        'expires_at' => now()->addHour(), // Link hết hạn sau 1 giờ
    ]);
    
    // Redirect trực tiếp đến đường dẫn tải xuống
    return redirect()->route('download.file', ['token' => $token]);
})->name('download-resource');

// Thêm routes cho thông báo
Route::middleware(['auth'])->group(function() {
    // Trang hiển thị tất cả thông báo
    Route::get('/notifications', 'App\Modules\Tuongtac\Controllers\TNoticeController@index')->name('notifications.index');
    
    // Đánh dấu thông báo đã đọc
    Route::post('/notices/mark-as-read/{id}', 'App\Modules\Tuongtac\Controllers\TNoticeController@markAsRead');
    
    // Đánh dấu tất cả thông báo đã đọc
    Route::post('/notices/mark-all-read', 'App\Modules\Tuongtac\Controllers\TNoticeController@markAllAsRead');
    
    // Lấy số lượng thông báo chưa đọc
    Route::get('/notices/count-unread', 'App\Modules\Tuongtac\Controllers\TNoticeController@countUnread');
    
    // Lấy danh sách thông báo
    Route::get('/notices/get-notice', 'App\Modules\Tuongtac\Controllers\TNoticeController@getNotice');
});

// Tuongtac routes
Route::post('/tblog/comment', [TCommentController::class, 'store'])->name('front.tblog.comment');
Route::post('/tblog/like', [TMotionController::class, 'toggleLike'])->name('front.tblog.like');
Route::post('/tblog/bookmark', [TRecommendController::class, 'toggleBookmark'])->name('front.tblog.bookmark');

// API endpoints
Route::get('/api/books/{id}/resources', [App\Http\Controllers\Frontend\BookFrontendController::class, 'getBookResources'])->name('api.books.resources');

