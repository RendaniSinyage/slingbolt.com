<?php

use Illuminate\Support\Facades\Route;
use Modules\Lending\Http\Controllers\LendingController;
use Modules\Lending\Http\Controllers\LoanProductController;
use Modules\Lending\Http\Controllers\LoanApplicationController;
use Modules\Lending\Http\Controllers\LoanRepaymentController;
use Modules\Lending\Http\Controllers\LoanSecurityController;
use Modules\Lending\Http\Controllers\LoanRestructureController;
use Modules\Lending\Http\Controllers\LoanSecurityReleaseController;
use Modules\Lending\Http\Controllers\LoanWriteOffController;
use Modules\Lending\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('lending')->middleware(['auth', 'check.lending.plan'])->name('lending.')->group(function() {
    Route::get('/', [LendingController::class, 'index'])->name('index');

    // Resourceful Routes
    Route::resource('loan-products', LoanProductController::class);
    Route::resource('loan-applications', LoanApplicationController::class);
    Route::resource('loan-securities', LoanSecurityController::class);
    Route::resource('loans', LendingController::class);

    // Nested Resourceful Routes
    Route::resource('loans.repayments', LoanRepaymentController::class)->only(['create', 'store']);
    Route::resource('loans.restructures', LoanRestructureController::class)->only(['create', 'store']);
    Route::resource('loans.write-offs', LoanWriteOffController::class)->only(['create', 'store']);
    Route::resource('security-assignments.releases', LoanSecurityReleaseController::class)->only(['create', 'store']);

    // Reports
    Route::get('reports/portfolio', [ReportController::class, 'portfolio'])->name('reports.portfolio');
    Route::get('reports/collections', [ReportController::class, 'collections'])->name('reports.collections');
});
