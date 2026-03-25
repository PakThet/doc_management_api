<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DocumentCategoryController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentPrefixController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeDocumentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\DocumentGroupController;
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


Route::prefix('employees')->group(function () {
 
    // ─── Employee CRUD ────────────────────────────────────────────────────────
    Route::get('/',                         [EmployeeController::class, 'index']);
    Route::post('/',                        [EmployeeController::class, 'store']);
    Route::get('/{employee}',               [EmployeeController::class, 'show']);
    Route::post('/{employee}',              [EmployeeController::class, 'update']);
    Route::delete('/{employee}',            [EmployeeController::class, 'destroy']);
 
    // ─── Toggle Status ────────────────────────────────────────────────────────
    Route::patch('/{employee}/status',      [EmployeeController::class, 'toggleStatus']);
 
    // ─── Employee Documents ───────────────────────────────────────────────────
    Route::get('/{employeeId}/documents',           [EmployeeDocumentController::class, 'index']);
    Route::post('/{employeeId}/documents',          [EmployeeDocumentController::class, 'upload']);
    Route::get('/documents/{id}',                   [EmployeeDocumentController::class, 'show']);
    Route::get('/documents/{id}/download',          [EmployeeDocumentController::class, 'download']);
    Route::delete('/documents/{id}',                [EmployeeDocumentController::class, 'destroy']);
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

    // ── Branches ───────────────────────────────────────────────────────────────
    Route::apiResource('branches', BranchController::class);
    Route::post('branches/{id}/restore', [BranchController::class, 'restore'])
        ->name('branches.restore');
    Route::patch('branches/{branch}/status', [BranchController::class, 'toggleStatus']);

    // ── Departments ────────────────────────────────────────────────────────────
    Route::apiResource('departments', DepartmentController::class);
    Route::post('departments/{id}/restore', [DepartmentController::class, 'restore'])
        ->name('departments.restore');

    // ── Employees ──────────────────────────────────────────────────────────────
    Route::apiResource('employees', EmployeeController::class);

    Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore']);



    // ── Users ──────────────────────────────────────────────────────────────────
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/restore', [UserController::class, 'restore'])
        ->name('users.restore');
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])
        ->name('users.assign-role');

    // ── Document Positions ────────────────────────────────────────────────────
    Route::apiResource('positions', PositionController::class);
    // ── Document Categories ────────────────────────────────────────────────────
    Route::apiResource('document-categories', DocumentCategoryController::class);
    Route::post('document-categories/{id}/restore', [DocumentCategoryController::class, 'restore'])
        ->name('document-categories.restore');

    // ── Document Prefixes ──────────────────────────────────────────────────────
    Route::apiResource('document-prefixes', DocumentPrefixController::class);
    Route::get(
        'document-prefixes/{documentPrefix}/generate',
        [DocumentPrefixController::class, 'generate']
    );

    // ── Document Groups ─────────────────────────────────────────────────────────
    Route::apiResource('document-groups', DocumentGroupController::class);
    Route::post('document-groups/{group}/restore', [DocumentGroupController::class, 'restore']);

    // ── Documents ──────────────────────────────────────────────────────────────
    // Standard CRUD for documents
    Route::apiResource('documents', DocumentController::class);
    Route::post('documents/{document}/restore', [DocumentController::class, 'restore']);
    Route::post('documents/{document}/move', [DocumentController::class, 'move']); // Move document to another group

    // ── Nested documents inside a group ─────────────────────────────────────────
    // Automatically assigns group_id from URL
    Route::prefix('document-groups/{group}')->group(function () {
        Route::get('documents', [DocumentController::class, 'index'])->name('groups.documents.index'); // List documents in this group
        Route::post('documents', [DocumentController::class, 'store'])->name('groups.documents.store'); // Create document inside this group
    });

    // ── Roles & Permissions (super-admin only) ─────────────────────────────────
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/addPermission', [RoleController::class, 'addPermission']);
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions']);
    Route::get('roles-permission-matrix', [RoleController::class, 'matrix']);
    Route::apiResource('permissions', PermissionController::class)->only(['index']);
    Route::get('available-roles', [RoleController::class, 'availableRoles']);
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
