<?php

use Illuminate\Support\Facades\Route;
use Modules\Lending\Http\Controllers\LendingController;
use Modules\Lending\Http\Controllers\LoanProductController;
use Modules\Lending\Http\Controllers\LoanApplicationController;
use Modules\Lending\Http\Controllers\LoanRepaymentController;
use Modules\Lending\Http\Controllers\LoanSecurityController;
use Modules\Lending\Http\Controllers\LoanRestructureController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('lending')->name('lending.')->group(function() {
    Route::get('/', [LendingController::class, 'index'])->name('index');

    Route::resource('loan-products', LoanProductController::class);
    Route::resource('loan-applications', LoanApplicationController::class);
    Route::resource('loan-securities', LoanSecurityController::class);

    Route::resource('loans', LendingController::class);
    Route::resource('loans.repayments', LoanRepaymentController::class)->only(['create', 'store']);
    Route::resource('loans.restructures', LoanRestructureController::class)->only(['create', 'store']);
});
