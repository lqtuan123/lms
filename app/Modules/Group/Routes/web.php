<?php

use Illuminate\Support\Facades\Route;
 
// Define routes here
use App\Modules\Group\Controllers\GroupController;
use App\Modules\Group\Controllers\GroupTypeController;
use App\Modules\Group\Controllers\GroupRoleController;


use App\Modules\Group\Controllers\GroupFolderController;
use App\Modules\Group\Controllers\FrontGroupController;
use App\Modules\Group\Controllers\GroupMemberController;

Route::group( ['prefix'=>'front/'  , 'as' => 'front.' ],function(){
    Route::get('groups',[FrontGroupController::class,'frontGroupShow'])->name('groups.show');
    
});
Route::group( ['prefix'=>'admin/'  , 'as' => 'admin.' ],function(){
   
    ///BlogCategory section
    Route::resource('grouptype',  GroupTypeController::class);
    Route::post('grouptype_status',[GroupTypeController::class,'grouptypeStatus'])->name('grouptype.status');
    Route::get('grouptype_search',[GroupTypeController::class,'grouptypeSearch'])->name('grouptype.search');
    
    Route::resource('grouprole',  GroupRoleController::class);
    Route::post('grouprole_status',[GroupRoleController::class,'grouproleStatus'])->name('grouprole.status');
    Route::get('grouprole_search',[GroupRoleController::class,'grouproleSearch'])->name('grouprole.search');
    

    // Route::resource('groupblog',  GroupBlogController::class);

    Route::resource('groupmember',  GroupMemberController::class);
    Route::post('groupmember_status',[GroupMemberController::class,'groupmemberStatus'])->name('groupmember.status');
    Route::get('groupmember_search',[GroupMemberController::class,'groupmemberSearch'])->name('groupmember.search');
   
    // Route::resource('groupfolder',  GroupFolderController::class);

    Route::resource('group',  GroupController::class);
    Route::post('group_status',[GroupController::class,'groupStatus'])->name('group.status');
    Route::get('group_search',[GroupController::class,'groupSearch'])->name('group.search');
    Route::get('group_member/{slug}',[GroupMemberController::class,'groupMemberList'])->name('group.members');
    Route::get('group_addmember/{slug}',[GroupMemberController::class,'groupAddMember'])->name('groupmember.addmember');
   
  

});
 