<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'application' => config('app.name'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::view('/app/{any?}', 'app')
->where('any', '.*');

Route::post(
    '/login',
    [AuthController::class, 'login']
)->middleware('throttle:login');

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)->middleware('auth');