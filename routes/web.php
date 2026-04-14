<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorksiteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LineWebhookController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/line/webhook', LineWebhookController::class)->name('line.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Attendance Core
    Route::get('/attendance/check-in-out', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    
    // Admin & HR Only Routes
    Route::middleware(['role:admin,hr'])->group(function () {
        // Employee Management
        Route::resource('employees', EmployeeController::class);

        // Worksite Management (สาขา)
        Route::resource('worksites', WorksiteController::class);

        // Departments & Positions
        Route::resource('departments', DepartmentController::class)->except(['create', 'show', 'edit']);
        Route::resource('positions', PositionController::class)->except(['create', 'show', 'edit']);
        Route::resource('shifts', ShiftController::class)->except(['show']);

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/leaves', [ReportController::class, 'leaves'])->name('reports.leaves');
        Route::get('/reports/leaves/export', [ReportController::class, 'exportLeaves'])->name('reports.leaves.export');

        // Settings - Leave Policies CRUD
        Route::get('/settings/leave', [SettingController::class, 'leaveSettings'])->name('settings.leave');
        Route::post('/settings/leave/policies', [SettingController::class, 'storePolicy'])->name('settings.leave.store');
        Route::put('/settings/leave/policies/{policy}', [SettingController::class, 'updatePolicy'])->name('settings.leave.update');
        Route::delete('/settings/leave/policies/{policy}', [SettingController::class, 'destroyPolicy'])->name('settings.leave.destroy');
    });

    // Leave Requests
    Route::resource('leave', LeaveController::class);

    // Leave Approvals (Supervisor, Admin, HR)
    Route::get('/leave-approvals', [LeaveApprovalController::class, 'index'])->name('leave.approvals');
    Route::post('/leave/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leave.approve');
    Route::post('/leave/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leave.reject');
    
    // Overtime Requests (Stubs)
    // Route::resource('overtime', OvertimeController::class);
    
    // Assignments (Stubs)
    // Route::resource('assignments', AssignmentController::class);
});

require __DIR__.'/auth.php';
