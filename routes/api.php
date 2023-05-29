<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ResetPasswordController;

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

//Signup and Login

Route::post('/register_user', [AuthController::class, 'register']);
Route::post('/login_user', [AuthController::class, 'login']);


//reset password

Route::post('/forgotPassword', [ResetPasswordController::class, 'forgotPassword']);

Route::post('/resetPassword', [ResetPasswordController::class, 'resetPassword']);


// get information or search them

Route::get('/posts', [PostController::class, 'get_Post']);

Route::get('/search_post/{name_post}', [PostController::class, 'search_post']);
Route::get('/search_user/{name_user}', [UserController::class, 'search_user']);

// patron or craftsman

Route::group(['middleware' => 'auth:sanctum'], function () {
    //post CRUD
    Route::post('/post/create', [PostController::class, 'store']);
    Route::post('/post/update/{id}', [PostController::class, 'update_post']);
    Route::delete('/post/delete/{id}', [PostController::class, 'delete_post']);

    //read notification
    Route::put('/notifications/{notification}', [PostController::class, 'markAsRead']);

    // update information  user or rate them
    Route::post('/update_user/{id}', [AuthController::class, 'update_user']);
    Route::post('/ratings/{id}', [UserController::class, 'rateUser']);

    Route::post('/update_password/{id}', [AuthController::class, 'update_password']);


    Route::get('/user', [AuthController::class, 'userDetails']);



    // loggout account
    Route::post('/logout_user', [AuthController::class, 'logout_user']);
});





//admin
Route::post('/login_admin', [AdminController::class, 'login']);

// نقدر نستغني على بريفكس ادمين كي شغل بركا اسم روت يعني تقدر تديرها ولا متديرهاش نورمال تبين بركا بلي راهي تع ادمين

Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {
    Route::post('/logout_admin', [AdminController::class, 'logout_admin']);

    Route::post('/ban_user', [UserController::class, 'ban']);
    Route::post('/unban_user/{id}', [UserController::class, 'unban']);
    Route::get('/getAlluser', [UserController::class, 'get_user']);
    Route::delete('/delete_user/{id}', [UserController::class, 'delete_user']);
    Route::delete('/delete_post_from_admin/{id}', [UserController::class, 'delete_post']);

});





?>
