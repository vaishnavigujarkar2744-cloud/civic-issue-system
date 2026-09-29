<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Citizen;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Staff;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Notifications (all roles)
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    // Citizen
    Route::middleware('role:citizen')->prefix('citizen')->name('citizen.')->group(function () {
        Route::get('/complaints', [Citizen\ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/create', [Citizen\ComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints', [Citizen\ComplaintController::class, 'store'])->name('complaints.store');
        Route::get('/complaints/{complaint}', [Citizen\ComplaintController::class, 'show'])->name('complaints.show');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [Admin\DashboardController::class, 'analytics'])->name('analytics');
        Route::get('/complaints', [Admin\ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [Admin\ComplaintController::class, 'show'])->name('complaints.show');
        Route::post('/complaints/{complaint}/assign', [Admin\ComplaintController::class, 'assign'])->name('complaints.assign');
        Route::get('/departments/{department}/staff', [Admin\ComplaintController::class, 'staffByDepartment'])->name('departments.staff');
    });

    // Department staff
    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/complaints', [Staff\ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [Staff\ComplaintController::class, 'show'])->name('complaints.show');
        Route::patch('/complaints/{complaint}/status', [Staff\ComplaintController::class, 'updateStatus'])->name('complaints.status');
    });
});
