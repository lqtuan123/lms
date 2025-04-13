<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Tuongtac\Controllers\TCommentController;
use App\Modules\Tuongtac\Controllers\TNoticeController;
use App\Modules\Tuongtac\Controllers\TBlogController;
use App\Modules\Tuongtac\Controllers\TMotionItemController;
use App\Modules\Tuongtac\Controllers\TRecommendController;
use App\Modules\Tuongtac\Controllers\TVoteController;
use App\Modules\Tuongtac\Controllers\TPageController;
use App\Modules\Tuongtac\Controllers\TUserpageController;
use App\Modules\Tuongtac\Controllers\TSurveyController;
use App\Modules\Tuongtac\Controllers\TPollController;
use App\Modules\Tuongtac\Controllers\AdminTBlogController;
// Define routes here

Route::group(['prefix'=>'admin', 'middleware' => 'admin.auth', 'as' => 'admin.'], function(){
    Route::resource('tblogs', AdminTBlogController::class);
    Route::post('tblog-status', [AdminTBlogController::class, 'blogStatus'])->name('tblogs.status');
    Route::delete('tblogs/{blogId}/resource/{resourceId}', [AdminTBlogController::class, 'removeResource'])->name('tblogs.resource.destroy');
});

Route::group( [    'as' => 'front.' ],function(){
    // Route::resource('tcomment',  TCommentController::class);
    Route::post('tcomment_save',[TCommentController::class,'saveComment'])->name('tcomments.savecomment');
    Route::post('tcomment_update',[TCommentController::class,'updateComment'])->name('tcomments.updatecomment');
    Route::post('tcomment_delete',[TCommentController::class,'deleteComment'])->name('tcomments.deletecomment');
  
    Route::post('/tnotice/mark-as-read/{id}', [TNoticeController::class, 'markAsRead'])->name('tnotice.markAsRead');
    Route::get('tnotice',[TNoticeController::class,'show'])->name('tnotice.show');
    // Route::get('/tblog', [ TBlogController::class, 'index'])->name('tblogs.index');
    Route::resource('tblogs',  TBlogController::class);
    Route::get('mytblogs',[TBlogController::class,'myblog'])->name('tblogs.myblog');
    Route::get('favtblogs',[TBlogController::class,'favblog'])->name('tblogs.favblog');
    Route::get('trendblogs',[TBlogController::class,'trendblog'])->name('tblogs.trendblog');
    Route::get('social/{tag}',[TBlogController::class,'tag'])->name('tblogs.tag');
    
    Route::get('pageblog/create/{id}',[TBlogController::class,'addgroupblog'])->name('groupblog.create');
    
    Route::get('pagesurvey/{slug}',[TPageController::class,'viewsurvey'])->name('pagesurvey.index');
    Route::get('surveys/{item_id}/{item_code}',[TSurveyController::class,'create'])->name('surveys.create');
    Route::post('surveys_store',[TSurveyController::class,'store'])->name('surveys.store');
    // Route::get('survey1/{$slug}',[TSurveyController::class,'show2'])->name('surveys.show');
    Route::get('surveyshow/{slug}',[TSurveyController::class,'xem'])->name('surveys.show');
    Route::get('surveyadd/{id}',[TSurveyController::class,'addquestion'])->name('surveys.addquestion');
    Route::post('survey_store',[TSurveyController::class,'savequestion'])->name('surveys.questionstore');
    
    Route::get('survey_edit/{id}',[TSurveyController::class,'editsurvey'])->name('surveys.editsurvey');
    Route::post('survey_update/{id}',[TSurveyController::class,'updatesurvey'])->name('surveys.updatesurvey');
    Route::post('survey_destroy/{id}',[TSurveyController::class,'destroysurvey'])->name('surveys.destroysurvey');
    
    Route::get('questions_edit/{id}',[TSurveyController::class,'editquestion'])->name('surveys.editquestion');
    Route::post('questions_update/{id}',[TSurveyController::class,'updatequestion'])->name('surveys.updatequestion');
    Route::get('questions_destroy/{id}',[TSurveyController::class,'destroyquestion'])->name('surveys.destroyquestion');
   
    Route::post('tblog-draft',[TBlogController::class,'draft'])->name('tblogs.draft');
    Route::get('tblog-status/{id}',[TBlogController::class,'status'])->name('tblogs.status');
    Route::get('/gettblog/{slug}', [TBlogController::class, 'getPostContent']);

    Route::post('/react', [TMotionItemController::class, 'react'])->name('reacts.react');
    Route::post('/bookmark', [TRecommendController::class, 'toggleBookmark'])->name('bookmarks.bookmark');
    Route::post('/vote', [TVoteController::class, 'vote'])->name('votes.vote');
    Route::get('/userpage/{slug}', [TUserpageController::class, 'viewuser'])->name('userpages.viewuser');
    Route::get('/editprofile', [TUserpageController::class, 'edituser'])->name('userpages.edituser');
    Route::post('/updateuser', [TUserpageController::class, 'updateuser'])->name('userpages.updateuser');
    Route::post('/changepass', [TUserpageController::class, 'changePassword'])->name('userpages.changepassword');
    
    Route::get('/vinh-danh-nguoi-dung', [TUserpageController::class, 'user_hornor'])->name('userpages.hornor');

    Route::get('/tpage/{slug}', [TPageController::class, 'view'])->name('tpage.view');
    Route::get('/groups/{slug}', [TPageController::class, 'viewgroup'])->name('tpage.viewgroup');
    Route::post('/updateimage', [TPageController::class, 'updateImage'])->name('tpage.updateimage');
    
    Route::get('/polls', [TPollController::class, 'index'])->name('poll.index');
    Route::post('/polls/vote', [TPollController::class, 'voteAll'])->name('poll.voteAll');
    
   
});