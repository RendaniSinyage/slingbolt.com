<?php

use Illuminate\Support\Facades\Route;
use Modules\Tenders\Http\Controllers\TenderController;

Route::group(['middleware' => ['web', 'auth', 'check.tenders.plan']], function () {
    Route::prefix('tenders')->name('tenders.')->group(function () {
        Route::get('/', [TenderController::class, 'index'])->name('index');
        Route::get('/settings', [TenderController::class, 'settings'])->name('settings');
        Route::post('/settings', [TenderController::class, 'settingsStore'])->name('settings.store');
        Route::get('/{id}/accept', [TenderController::class, 'accept'])->name('accept');
        Route::get('/{id}/deny', [TenderController::class, 'deny'])->name('deny');
    });
});
