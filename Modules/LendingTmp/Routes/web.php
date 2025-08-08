<?php

use Illuminate\Support\Facades\Route;
use Modules\LendingTmp\Http\Controllers\LendingController;
use Modules\LendingTmp\Http\Controllers\LoanProductController;
use Modules\LendingTmp\Http\Controllers\LoanApplicationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('lendingtmp')->name('lendingtmp.')->group(function() {
    Route::get('/', [LendingController::class, 'index'])->name('index');
    Route::resource('loans', LendingController::class);
    Route::resource('loan-products', LoanProductController::class);
    Route::resource('loan-applications', LoanApplicationController::class);
});
