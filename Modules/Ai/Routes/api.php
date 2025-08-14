<?php

use Illuminate\Support\Facades\Route;
use Modules\Ai\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| AI Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the AiServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1/ai')->group(function () {
    Route::post('chat', ChatController::class);
});
