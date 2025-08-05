<?php

use App\Http\Controllers\ExternalUserController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// OAuth2 endpoints
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])->name('passport.token');
Route::get('/oauth/tokens', [AuthorizedAccessTokenController::class, 'forUser'])->name('passport.tokens.index');
Route::delete('/oauth/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy'])->name('passport.tokens.destroy');
Route::get('/oauth/clients', [ClientController::class, 'forUser'])->name('passport.clients.index');
Route::post('/oauth/clients', [ClientController::class, 'store'])->name('passport.clients.store');
Route::put('/oauth/clients/{client_id}', [ClientController::class, 'update'])->name('passport.clients.update');
Route::delete('/oauth/clients/{client_id}', [ClientController::class, 'destroy'])->name('passport.clients.destroy');
Route::get('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'forUser'])->name('passport.personal.tokens.index');
Route::post('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])->name('passport.personal.tokens.store');
Route::delete('/oauth/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy'])->name('passport.personal.tokens.destroy');


Route::post('login', [ApiController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('logout', [ApiController::class, 'logout']);
    Route::get('get-projects', [ApiController::class, 'getProjects']);
    Route::post('add-tracker', [ApiController::class, 'addTracker']);
    Route::post('stop-tracker', [ApiController::class, 'stopTracker']);
    Route::post('upload-photos', [ApiController::class, 'uploadImage']);
});

/*
|--------------------------------------------------------------------------
| External Platform Integration Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['client.credentials'])->group(function () {
    Route::get('/external/check-user', [ExternalUserController::class, 'checkUser']);
    Route::post('/external/create-seller-company', [ExternalUserController::class, 'createSellerCompany']);
    Route::post('/external/link-seller', [ExternalUserController::class, 'linkExistingSeller']);
    Route::get('/external/user-by-external-id', [ExternalUserController::class, 'getUserByExternalId']);
    Route::put('/external/update-user', [ExternalUserController::class, 'updateExternalUser']);
    Route::post('/external/disconnect-user', [ExternalUserController::class, 'disconnectExternalUser']);
    Route::post('/external/bulk-sync-users', [ExternalUserController::class, 'bulkSyncExternalUsers']);
    Route::get('/external/stats', [ExternalUserController::class, 'getExternalUserStats']);
});
