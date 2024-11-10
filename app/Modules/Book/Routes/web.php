<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Book\Controllers\BookController;
use App\Modules\Book\Controllers\BookTypeController;

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    // Routes for Book
    Route::resource('books', BookController::class)->parameters(['books' => 'id'])->except('show');
    //Route::get('books/{id}', [BookController::class, 'show'])->where('id', '[0-9]+')->name('book.show');
    Route::post('books_status',[ BookController::class,'bookStatus'])->name('books.status');
    Route::get('books_search', [BookController::class, 'bookSearch'])->name('books.search');

});



