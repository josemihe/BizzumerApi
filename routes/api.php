<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\v1\GroupController;
use App\Http\Controllers\v1\GroupUserController;
use App\Http\Controllers\v1\MemberController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Public routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::put('/password-reset', [UserController::class, 'passwordReset']);
Route::put('/password-update', [UserController::class, 'passwordUpdate']);

//Protected routes
Route::group(['middleware'=>['auth:sanctum']], function() {
    Route::post('/logout', [UserController::class, 'logout']);
});
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\v1', 'middleware' => ['auth:sanctum']], function () {
    // GroupController endpoints
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/groups/create', [GroupController::class, 'create']);
    Route::get('/groups/{id}', [GroupController::class, 'show']);
    Route::get('/groups/edit/{id}', [GroupController::class, 'edit']);
    Route::put('/groups/{id}', [GroupController::class, 'update']);
    Route::post('/groups', [GroupController::class, 'store']);

    // MemberController endpoints
    Route::post('/join', [MemberController::class, 'store']);

    // GroupUserController endpoints
    Route::put('/groups/{group_id}/participants/{user_id}', [GroupUserController::class, 'update']);
    Route::delete('/groups/{group_id}/participants/{user_id}', [GroupUserController::class, 'destroy']);
});
