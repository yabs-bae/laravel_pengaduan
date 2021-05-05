<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ComplaintController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('user/photo', [UserController::class, 'updatePhoto']);
    Route::post('logout', [UserController::class, 'logout']);


    Route::get('complaint', [ComplaintController::class, 'index']);
    Route::post('complaint', [ComplaintController::class, 'store']);
    Route::post('complaint/update', [ComplaintController::class, 'update']);
    Route::post('complaint/delete', [ComplaintController::class, 'delete_batch']);

    Route::post('complaint/histories-status', [ComplaintController::class, 'update_status']);
    Route::post('complaint/histories-clear', [ComplaintController::class, 'clear_histories']);
    Route::post('complaint/histories-delete', [ComplaintController::class, 'delete_histories']);


});



Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);


