<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Tuongtac\Controllers\TCommentController;
use App\Modules\Tuongtac\Controllers\TNoticeController;
use App\Modules\Tuongtac\Controllers\TBlogController;
use App\Modules\Tuongtac\Controllers\TMotionItemController;
use App\Modules\Tuongtac\Controllers\TRecommendController;
use App\Modules\Tuongtac\Controllers\TVoteController;
use App\Modules\Tuongtac\Controllers\TPageController;
use App\Modules\Tuongtac\Controllers\TPollController;
use App\Modules\Tuongtac\Controllers\AdminTBlogController;
use App\Modules\Tuongtac\Controllers\TuongtacController;
use App\Modules\Tuongtac\Controllers\ChatController;
use App\Modules\Tuongtac\Controllers\TTagController;
use App\Modules\Tuongtac\Controllers\TSurveyController;
use App\Http\Controllers\Frontend\GroupFrontendController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\RatingController;
// Define routes here

Route::group(['prefix'=>'admin', 'middleware' => 'admin.auth', 'as' => 'admin.'], function(){
    Route::resource('tblogs', AdminTBlogController::class);
    Route::post('tblog-status', [AdminTBlogController::class, 'blogStatus'])->name('tblogs.status');
    Route::delete('tblogs/{blogId}/resource/{resourceId}', [AdminTBlogController::class, 'removeResource'])->name('tblogs.resource.destroy');
});

Route::group( [    'as' => 'front.' ],function(){
    // Route::resource('tcomment',  TCommentController::class);
    Route::post('tcomments/save',[TCommentController::class,'saveComment'])->name('comments.save');
    Route::post('tcomments/update',[TCommentController::class,'updateComment'])->name('comments.update');
    Route::post('tcomments/delete',[TCommentController::class,'deleteComment'])->name('comments.delete');
    Route::get('tcomments/{itemId}/{itemCode}',[TCommentController::class,'show'])->name('comments.show');
    
    // Social interactions routes
    Route::post('reactions/react', [TMotionItemController::class, 'react'])->name('reactions.react');
    Route::get('reactions/status', [TMotionItemController::class, 'getReactionStatus'])->name('reactions.status');
    Route::post('share', [TuongtacController::class, 'processShare'])->name('share');
  
    Route::post('/tnotice/mark-as-read/{id}', [TNoticeController::class, 'markAsRead'])->name('tnotice.markAsRead');
    Route::get('tnotice',[TNoticeController::class,'show'])->name('tnotice.show');
    // Route::get('/tblog', [ TBlogController::class, 'index'])->name('tblogs.index');
    Route::resource('tblogs',  TBlogController::class);
    Route::get('mytblogs',[TBlogController::class,'myblog'])->name('tblogs.myblog');
    Route::get('favtblogs',[TBlogController::class,'favblog'])->name('tblogs.favblog');
    Route::get('trendblogs',[TBlogController::class,'trendblog'])->name('tblogs.trendblog');
    Route::get('social/{tag}',[TBlogController::class,'tag'])->name('tblogs.tag');
    
    Route::get('pageblog/create/{id}',[TBlogController::class,'addgroupblog'])->name('groupblog.create');
    
    Route::post('tblog-draft',[TBlogController::class,'draft'])->name('tblogs.draft');
    Route::get('tblog-status/{id}',[TBlogController::class,'status'])->name('tblogs.status');
    Route::get('/gettblog/{slug}', [TBlogController::class, 'getPostContent']);

    Route::post('/react', [TMotionItemController::class, 'react'])->name('reacts.react');
    Route::post('/bookmark', [TRecommendController::class, 'toggleBookmark'])->name('bookmarks.bookmark');
    Route::post('/vote', [RatingController::class, 'store'])->name('votes.vote');
    Route::get('/editprofile', [ProfileController::class, 'edit'])->name('userpages.edituser');
    Route::post('/updateuser', [ProfileController::class, 'update'])->name('userpages.updateuser');
    Route::post('/changepass', [ProfileController::class, 'changePassword'])->name('userpages.changepassword');
    
    Route::get('/tpage/{slug}', [GroupFrontendController::class, 'show'])->name('tpage.view');
    Route::get('/groups/{slug}', [GroupFrontendController::class, 'index'])->name('tpage.viewgroup');
    Route::post('/updateimage', [GroupFrontendController::class, 'update'])->name('tpage.updateimage');
    
    Route::get('/polls', [TPollController::class, 'index'])->name('poll.index');
    Route::post('/polls/vote', [TPollController::class, 'voteAll'])->name('poll.voteAll');
    
    Route::get('/books/fetch-names', [\App\Http\Controllers\Frontend\BookFrontendController::class, 'fetchBookNames'])->name('books.fetchNames');
});