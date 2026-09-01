<?php
// Add these routes to routes/web.php

use App\Http\Controllers\EmployeeTaskTrackerController;
use Illuminate\Support\Facades\Route;

// Employee Task Tracker Routes
Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('employee-task-tracker', [EmployeeTaskTrackerController::class, 'index'])->name('employee.task.tracker');
    Route::get('employee-task-tracker/tasks/{employeeId}', [EmployeeTaskTrackerController::class, 'getEmployeeTasks'])->name('employee.task.tracker.tasks');
    Route::get('employee-task-tracker/stats', [EmployeeTaskTrackerController::class, 'getTaskStats'])->name('employee.task.tracker.stats');
    Route::put('employee-task-tracker/task/{taskId}/assign', [EmployeeTaskTrackerController::class, 'updateTaskAssignment'])->name('employee.task.tracker.update.assign');
    Route::post('employee-task-tracker/bulk-assign', [EmployeeTaskTrackerController::class, 'bulkAssign'])->name('employee.task.tracker.bulk.assign');
});
