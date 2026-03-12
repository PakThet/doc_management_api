<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DocumentCategoryController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentPrefixController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeDocumentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AchievementController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
// ── Public (no auth required) ──────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {

    Route::post('login',          [AuthController::class, 'login'])->name('login');
    Route::post('register',       [AuthController::class, 'register'])->name('register');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');

    // Email verification link (signed URL sent via email)
    Route::get(
        'verify-email/{id}/{hash}',
        [AuthController::class, 'verifyEmail']
    )->middleware('signed')->name('verify-email');
});

// ── Protected (requires Sanctum token) ────────────────────────────────────
Route::prefix('auth')->name('auth.')->middleware(['auth:api'])->group(function () {

    Route::post('logout',           [AuthController::class, 'logout'])->name('logout');
    Route::post('logout-all',       [AuthController::class, 'logoutAll'])->name('logout-all');
    Route::post('refresh',          [AuthController::class, 'refresh'])->name('refresh');
    Route::get('me',               [AuthController::class, 'me'])->name('me');
    Route::put('profile',          [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('change-password',  [AuthController::class, 'changePassword'])->name('change-password');
    Route::post('two-factor/toggle', [AuthController::class, 'toggleTwoFactor'])->name('two-factor.toggle');

    // Resend email verification
    Route::post(
        'email/verification-notification',
        [AuthController::class, 'resendVerification']
    )->middleware('throttle:6,1')->name('verification.resend');
});



// Public route — document verification via QR/token (no auth)
Route::get('documents/verify/{token}', [DocumentController::class, 'verify'])->name('documents.verify');

Route::middleware('auth:api')->group(function () {

    // ── Organizations ──────────────────────────────────────────────────────────
    Route::apiResource('organizations', OrganizationController::class);

    Route::post('organizations/{id}/restore', [OrganizationController::class, 'restore'])
        ->name('organizations.restore');

    // ── Branches ───────────────────────────────────────────────────────────────
    Route::apiResource('branches', BranchController::class);
    Route::post('branches/{id}/restore', [BranchController::class, 'restore'])
        ->name('branches.restore');

    // ── Departments ────────────────────────────────────────────────────────────
    Route::apiResource('departments', DepartmentController::class);
    Route::post('departments/{id}/restore', [DepartmentController::class, 'restore'])
        ->name('departments.restore');

    // ── Employees ──────────────────────────────────────────────────────────────
    Route::apiResource('employees', EmployeeController::class);

    Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore']);

    Route::prefix('employees/{employee}')->group(function () {

        Route::get('documents', [EmployeeDocumentController::class, 'index']);

        Route::post('documents', [EmployeeDocumentController::class, 'upload']);
    });

    Route::get('employee-documents/{id}/download', [EmployeeDocumentController::class, 'download']);

    Route::delete('employee-documents/{id}', [EmployeeDocumentController::class, 'destroy']);

    // ── Users ──────────────────────────────────────────────────────────────────
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/restore', [UserController::class, 'restore'])
        ->name('users.restore');
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])
        ->name('users.assign-role');
    Route::post('users/{user}/sync-permissions', [UserController::class, 'syncPermissions'])
        ->name('users.sync-permissions');
    Route::get('available-roles', [UserController::class, 'roles'])
        ->name('users.roles');

    // ── Document Categories ────────────────────────────────────────────────────
    Route::apiResource('document-categories', DocumentCategoryController::class);
    Route::post('document-categories/{id}/restore', [DocumentCategoryController::class, 'restore'])
        ->name('document-categories.restore');

    // ── Document Prefixes ──────────────────────────────────────────────────────
    Route::apiResource('document-prefixes', DocumentPrefixController::class);
    Route::post('document-prefixes/{id}/restore', [DocumentPrefixController::class, 'restore'])
        ->name('document-prefixes.restore');

    // ── Documents ──────────────────────────────────────────────────────────────
    Route::apiResource('documents', DocumentController::class);
    Route::post('documents/{id}/restore', [DocumentController::class, 'restore'])
        ->name('documents.restore');

    // ── Roles & Permissions (super-admin only) ─────────────────────────────────
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/addPermission', [RoleController::class, 'addPermission']);
    Route::post('roles/{role}/remove-permission', [RoleController::class, 'removePermission']);
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions']);
    Route::get('roles-permission-matrix', [RoleController::class, 'matrix']);
    Route::apiResource('permissions', PermissionController::class)->only(['index']);

    // ── Achievement ──────────────────────────────────────────────────────────
    Route::apiResource('achievements', AchievementController::class);
    // ── Activity Logs ──────────────────────────────────────────────────────────
    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index');
    Route::get('activity-logs/my', [ActivityLogController::class, 'myActivity'])
        ->name('activity-logs.my');
    Route::get('activity-logs/{activity}', [ActivityLogController::class, 'show'])
        ->name('activity-logs.show');
    Route::delete('activity-logs/{id}', [ActivityLogController::class, 'destroy'])
    ->middleware(['auth', 'permission:delete activity-logs']);
});
