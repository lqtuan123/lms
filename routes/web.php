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

// Authentication Routes
Route::get('/admin/login', [LoginController::class, 'viewlogin'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.checklogin');
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Admin Routes
Route::group(['prefix' => 'admin', 'middleware' => 'admin.auth', 'as' => 'admin.'], function () {
    Route::get('/', [AdminController::class, 'index'])->name('home');
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('user', UserController::class);
    Route::post('user/status', [UserController::class, 'userStatus'])->name('user.status');
    Route::get('user/search', [UserController::class, 'userSearch'])->name('user.search');
    Route::get('user/sort', [UserController::class, 'userSort'])->name('user.sort');
    Route::post('user/detail', [UserController::class, 'userDetail'])->name('user.detail');
    Route::post('user/profile', [UserController::class, 'userUpdateProfile'])->name('user.profile.update');
    Route::get('user/profile', [UserController::class, 'userViewProfile'])->name('user.profile.view');
    Route::get('user/jsearch', [UserController::class, 'userJsearch'])->name('user.jsearch');

    // Group Management
    Route::resource('ugroup', UGroupController::class);
    Route::post('ugroup/status', [UGroupController::class, 'ugroupStatus'])->name('ugroup.status');
    Route::get('ugroup/search', [UGroupController::class, 'ugroupSearch'])->name('ugroup.search');

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
Route::post('front/login', [IndexController::class, 'login'])->name('front.login.submit');
Route::get('front/register', [IndexController::class, 'viewRegister'])->name('front.register');
Route::post('front/register', [IndexController::class, 'saveUser'])->name('front.register.submit');
Route::post('/front/logout', [App\Http\Controllers\Frontend\LoginController::class, 'logout'])->name('logout');
Route::post('upload-ckeditor', [\App\Http\Controllers\Frontend\FilesFrontendController::class, 'ckeditorUpload'])->name('upload.ckeditor');

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
    Route::post('/avatar-upload', [FilesFrontendController::class, 'avatarUpload'])->name('front.upload.avatar');
});

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

