<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Exercise\Controllers\TracNghiemCauHoiController;
use App\Modules\Exercise\Controllers\TuLuanCauHoiController;
use App\Modules\Exercise\Controllers\BoDeTracNghiemController;
use App\Modules\Exercise\Controllers\BoDeTuLuanController;
use App\Modules\Exercise\Controllers\NoidungPhancongController;

// Định nghĩa route cho module câu hỏi
// Route::prefix('admin/tuluancauhoi')->name('admin.tuluancauhoi.')->group(function () {
//     Route::get('/', [TuluancauhoiController::class, 'index'])->name('index');
//     Route::get('/create', [TuluancauhoiController::class, 'create'])->name('create');
//     Route::post('/store', [TuluancauhoiController::class, 'store'])->name('store');
//     Route::get('/{id}/edit', [TuluancauhoiController::class, 'edit'])->name('edit');
//     Route::patch('/{id}', [TuluancauhoiController::class, 'update'])->name('update');
//     Route::delete('/{id}', [TuluancauhoiController::class, 'destroy'])->name('destroy');
//     Route::get('/search', [TuluancauhoiController::class, 'search'])->name('search');
//     Route::get('/{id}', [TuluancauhoiController::class, 'show'])->name('show');
// });

Route::prefix('admin/noidung_phancong')->name('admin.noidung_phancong.')->group(function () {
    Route::get('/', [NoidungPhancongController::class, 'index'])->name('noidung_phancong.index');
    Route::get('/create', [NoidungPhancongController::class, 'create'])->name('noidung_phancong.create');
    Route::post('/store', [NoidungPhancongController::class, 'store'])->name('noidung_phancong.store');
    Route::get('/{id}/edit', [NoidungPhancongController::class, 'edit'])->name('noidung_phancong.edit');
    Route::put('/{id}', [NoidungPhancongController::class, 'update'])->name('noidung_phancong.update');
    Route::delete('/{id}', [NoidungPhancongController::class, 'destroy'])->name('noidung_phancong.destroy');
    Route::delete('/noidung-phancong/{noidungPhancongId}/resource/{resourceId}', 
    [NoidungPhancongController::class, 'destroyResource']
)->name('noidung_phancong.resource.destroy');

});

Route::group(['prefix' => 'admin/', 'as' => 'admin.'], function () {
    Route::resource('tracnghiemcauhoi', TracNghiemCauHoiController::class);
    // Route::get('hocphan_search', [App\Modules\Exercise\Controllers\::class, 'moduleSearch'])->name('hocphan.search');
    Route::delete('/admin/tracnghiemcauhoi/{tracnghiemcauhoiId}/resource/{resourceId}', [TracNghiemCauHoiController::class, 'removeResource'])->name('tracnghiemcauhoi.removeResource');

    Route::resource('tuluancauhoi', TuLuanCauHoiController::class);
    Route::resource('noidung_phancong', NoidungPhancongController::class);
    Route::delete('/admin/tuluancauhoi/{tuluancauhoiId}/resource/{resourceId}', [TuLuanCauHoiController::class, 'removeResource'])->name('tuluancauhoi.removeResource');
    
    // Bộ đề trắc nghiệm
    Route::resource('bode_tracnghiem', BoDeTracNghiemController::class);
    // Bộ đề tu luan
    Route::resource('bode_tuluans', BoDeTuLuanController::class);
});

