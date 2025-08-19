<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Workdo\Notes\Http\Controllers\NotesController;

Route::middleware(['web','auth','verified','PlanModuleCheck:Notes'])->group(function () {

    Route::resource('notes', NotesController::class);
});
