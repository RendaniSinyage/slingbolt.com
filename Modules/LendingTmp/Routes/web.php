<?php

use Illuminate\Support\Facades\Route;
use Modules\LendingTmp\Http\Controllers\LendingController;
use Modules\LendingTmp\Http\Controllers\LoanProductController;
use Modules\LendingTmp\Http\Controllers\LoanApplicationController;
use Modules\LendingTmp\Http\Controllers\LoanRepaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('lendingtmp')->name('lendingtmp.')->group(function() {
    Route::get('/', [LendingController::class, 'index'])->name('index');

    Route::resource('loan-products', LoanProductController::class);
    Route::resource('loan-applications', LoanApplicationController::class);

    Route::resource('loans', LendingController::class);
    Route::resource('loans.repayments', LoanRepaymentController::class)->only(['create', 'store']);
});
