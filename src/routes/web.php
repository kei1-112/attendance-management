<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

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
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [AuthenticatedSessionController  ::class, 'destroy']);
Route::middleware('auth')->group(function(){
    Route::get('/attendance', [AttendanceController::class, 'attendance']);
});
Route::middleware('auth')->group(function(){
    Route::post('/attendance', [AttendanceController::class, 'store']);
});
Route::middleware('auth')->group(function(){
    Route::post('/rest_start', [AttendanceController::class, 'restStart']);
});
Route::middleware('auth')->group(function(){
    Route::post('/rest_end', [AttendanceController::class, 'restEnd']);
});
Route::middleware('auth')->group(function(){
    Route::post('/leave', [AttendanceController::class, 'leave']);
});
Route::middleware('auth')->group(function(){
    Route::get('/attendance_list', [AttendanceController::class, 'list']);
});
Route::middleware('auth')->group(function(){
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail']);
});