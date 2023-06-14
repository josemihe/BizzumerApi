<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\v2\GroupController;
use App\Http\Controllers\v2\GroupUserController;
use App\Http\Controllers\v2\ExpenseController;
use App\Http\Controllers\v2\ImageController;
use App\Http\Controllers\v2\MemberController;
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
Route::group(['middleware'=>['api_token']], function() {
    Route::delete('/logout', [UserController::class, 'logout']);
});
/*Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\v1', 'middleware' => ['api_token']], function () {
    // GroupController endpoints
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/group', [GroupController::class, 'show']);
    Route::post('/group/create', [GroupController::class, 'store']);

    // GroupUserController endpoints
    //Route::put('/groups/{group_id}/participants/{user_id}', [GroupUserController::class, 'update']);
    Route::delete('/groups/participant/', [GroupUserController::class, 'destroy']);
    Route::delete('/groups/leave', [GroupUserController::class, 'leaveGroup']);
});*/
Route::group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers\v2', 'middleware' => ['api_token']], function () {
    // GroupController endpoints
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/group', [GroupController::class, 'show']);
    Route::post('/group/create', [GroupController::class, 'store']);
    // GroupUserController endpoints
    Route::delete('/groups/participant/', [GroupUserController::class, 'destroy']);
    Route::delete('/groups/leave', [GroupUserController::class, 'leaveGroup']);
    // MemberController endpoints
    Route::post('join', [MemberController::class, 'store']);
    Route::post('send-invite', [MemberController::class, 'sendAccessMail']);
    Route::get('group/expenses', [ExpenseController::class, 'showExpenses']);
    Route::post('group/upload-expense', [ExpenseController::class, 'uploadExpense']);
    Route::get('group/calculate-expenses', [ExpenseController::class, 'calculateExpenses']);
    Route::delete('group/delete-expense', [ExpenseController::class, 'deleteExpense']);
    //ImageController endpoints
    Route::get('/expense/view-image', [ImageController::class,'index'])->name('welcome');
    Route::post('/expense/upload-image', [ImageController::class,'store']);
});

