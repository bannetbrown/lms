<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\Api\ApiController;

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

Route::get('auth/google/url', [ApiController::class, 'getGoogleAuthUrl']);
Route::post('auth/google/callback', [ApiController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/all-goal-list', [ApiController::class, 'goallist']);
    Route::get('/learning-sequence-list', [ApiController::class, 'learningsequencelist']);


});
