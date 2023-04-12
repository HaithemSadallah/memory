<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register_user', [AuthController::class, 'register']);
Route::post('/login_user', [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'get_Post']);
Route::get('/user', [UserController::class, 'get_user']);
Route::get('/search_post/{name_post}', [PostController::class, 'search_post']);
Route::get('/search_user/{name_user}', [UserController::class, 'search_user']);

// patron or craftsman

Route::group(['middleware' => 'auth:sanctum'], function () {
    //post
    Route::post('/logout_user', [AuthController::class, 'logout_user']);
    Route::post('/post/create', [PostController::class, 'store']);
    Route::post('/post/update/{id}', [PostController::class, 'update_post']);
    Route::delete('/post/delete/{id}', [PostController::class, 'delete_post']);
    //image
    Route::post('/uploadImages_post', [PostController::class, 'uploadImages']);
    Route::post('/updateImages_post/{id}', [PostController::class, 'updateImages']);
    Route::delete('/deleteImages_post/{id}', [PostController::class, 'deleteImages']);

    //notification
    Route::put('/notifications/{notification}', [PostController::class, 'markAsRead']);

    //  user
    Route::post('/update_user', [AuthController::class, 'update_user']);
    Route::put('/update_password', [AuthController::class, 'update_password']);
});





//admin
Route::post('/login_admin', [AdminController::class, 'login']);

// نقدر نستغني على بريفكس ادمين كي شغل بركا اسم روت يعني تقدر تديرها ولا متديرهاش نورمال تبين بركا بلي راهي تع ادمين

Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {
    Route::post('/logout_admin', [AdminController::class, 'logout_admin']);

    Route::post('/ban_user', [UserController::class, 'ban']);
    Route::post('/unban_user/{id}', [UserController::class, 'unban']);

    Route::delete('/delete_user/{id}', [UserController::class, 'delete_user']);

});





?>
