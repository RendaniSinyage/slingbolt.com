<?php

use Illuminate\Support\Facades\Route;
use Modules\Ai\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| AI Module Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the AiServiceProvider.
|
*/

Route::middleware('auth')->prefix('ai')->group(function() {
    Route::get('chat', [ChatController::class, 'showChatPage'])->name('ai.chat');
});
