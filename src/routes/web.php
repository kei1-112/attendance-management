<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\EmailVerificationController;
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
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');

Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');

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
    Route::get('/attendance/list', [AttendanceController::class, 'list']);
});
Route::middleware('auth')->group(function(){
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail']);
});
Route::middleware('auth')->group(function(){
    Route::post('/stamp_correction_request', [RequestController::class, 'request']);
});

Route::get('/stamp_correction_request/list', function (Request $request) {
    if (Auth::guard('admin')->check()) {
        return app(AdminRequestController::class)->list($request);
    } elseif (Auth::guard('web')->check()) {
        return app(RequestController::class)->list($request);
    } else {
        return redirect('/login');
    }
});


// 管理者が未ログインのときのみアクセス可
Route::middleware('guest:admin')->group(function () {
    Route::get('admin/login', [AuthenticatedSessionController::class, 'create']);
    Route::post('admin/login', [AdminController::class, 'login'])->name('admin.login');
});

// 管理者がログインしているときのみアクセス可
Route::middleware('auth:admin')->group(function () {
    Route::get('admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
});

Route::middleware('auth:admin')->group(function () {
    Route::get('admin/attendance/{id}', [AdminAttendanceController::class, 'detail']);
});

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/stamp_correction', [AdminAttendanceController::class, 'correct']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/staff/list', [StaffController::class, 'list']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/attendance/staff/{id}', [StaffController::class, 'attendance']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'detail']);
});

Route::middleware('auth:admin')->group(function () {
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'correct']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/attendance/export', [AdminAttendanceController::class, 'export']);
});