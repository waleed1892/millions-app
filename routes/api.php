<?php

use App\Http\Controllers\PostController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

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

Route::post('/tokens/create', function (Request $request) {
    $credentials = ['email' => $request->email, 'password' => $request->password];
    if (Auth::attempt($credentials)) {
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('millions');
        return ['token' => $token->plainTextToken];
    } else {
        return 'Invalid credentials';
    }

});
Route::post('/users/create', function (Request $request) {
    $user = new User();
    $input = $request->all();
    $input['password'] = Hash::make($input['password']);
    $user = User::create($input);
    return $user;
});
Route::get('/posts', [PostController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class)->except(['index']);
    Route::post('/posts/toggleLike', [PostController::class, 'toggleLike']);
});
