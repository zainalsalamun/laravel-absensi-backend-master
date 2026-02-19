<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QrAbsenController;
use App\Models\User;
use App\Models\Company;
use App\Models\Attendance;
use App\Models\Permission;
use App\Models\QrAbsen;
use App\Models\Note;

Route::get('/', function () {
    return view('pages.auth.auth-login');
});

Route::middleware(['auth'])->group(function () {


Route::get('home', function () {
    $currentDate = date('Y-m-d');
    
    // Calculate late attendees
    $late_today = Attendance::where('date', $currentDate)
        ->whereHas('user', function ($query) {
            $query->whereHas('shift', function ($subQuery) {
                // Ensure we compare against shift time
            });
        })
        ->get()
        ->filter(function ($attendance) {
            return $attendance->user->shift && $attendance->time_in > $attendance->user->shift->time_in;
        })
        ->count();

    return view('pages.dashboard', [
        'type_menu' => 'home',
        'users_count' => User::count(),
        'shifts_count' => App\Models\Shift::count(),
        'present_today' => Attendance::where('date', $currentDate)->count(),
        'late_today' => $late_today,
        'latest_attendances' => Attendance::with('user')->orderBy('created_at', 'desc')->take(5)->get(),
        'latest_permissions' => Permission::with('user')->orderBy('created_at', 'desc')->take(5)->get(),
    ]);
})->name('home');

    Route::resource('users', UserController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('qr_absens', QrAbsenController::class);
    Route::resource('verifikasi', AttendanceController::class);
    Route::resource('reimbursements', App\Http\Controllers\ReimbursementController::class);
    Route::resource('shifts', App\Http\Controllers\ShiftController::class);

    Route::get('/qr-absens/{id}/download', [QrAbsenController::class, 'downloadPDF'])->name('qr_absens.download');
});
