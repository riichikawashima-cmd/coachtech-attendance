<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', function () {
        return redirect('/attendance');
    });

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::get('/attendance/list', [AttendanceListController::class, 'index'])
        ->name('attendance.list');

    Route::get('/attendance/{date}', [AttendanceDetailController::class, 'index'])
        ->name('attendance.detail');

    Route::post('/attendance/{date}/apply', [AttendanceDetailController::class, 'apply'])
        ->name('attendance.apply');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart']);
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
});

Route::get('/admin/login', [AdminAuthenticatedSessionController::class, 'create']);
Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store']);

Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
    ->middleware(['auth', 'verified']);

Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
    ->middleware(['auth', 'verified']);

Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
    ->middleware(['auth', 'verified']);

Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'show'])
    ->middleware(['auth', 'verified']);

Route::get('/admin/attendance/staff/{id}/csv', [AdminStaffController::class, 'csv'])
    ->middleware(['auth', 'verified']);

Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/admin/login');
});