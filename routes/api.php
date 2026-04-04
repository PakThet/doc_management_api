<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DocumentCategoryController;
use App\Http\Controllers\Api\DocumentPrefixController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/documents/verify/{token}', [DocumentController::class, 'verify'])->name('documents.verify');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me/avatar', [AuthController::class, 'updateAvatar']);
    Route::put('/me/password', [AuthController::class, 'updatePassword']);

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:view users')->get('/users', [UserController::class, 'index']);
    Route::middleware('permission:view users')->get('/users/{user}', [UserController::class, 'show']);
    Route::middleware('permission:create users')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:edit users')->put('/users/{user}', [UserController::class, 'update']);
    Route::middleware('permission:delete users')->delete('/users/{user}', [UserController::class, 'destroy']);
    Route::middleware('permission:edit users')->post('/users/{user}/roles', [UserController::class, 'assignRoles']);

    /*
    |--------------------------------------------------------------------------
    | Organizations
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:view organizations')->get('/organizations', [OrganizationController::class, 'index']);
    Route::middleware('permission:create organizations')->post('/organizations', [OrganizationController::class, 'store']);
    Route::middleware('permission:view organizations')->get('/organizations/{organization}', [OrganizationController::class, 'show']);
    Route::middleware('permission:edit organizations')->put('/organizations/{organization}', [OrganizationController::class, 'update']);
    Route::middleware('permission:delete organizations')->delete('/organizations/{organization}', [OrganizationController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Branches
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:view branches')->get('/branches', [BranchController::class, 'index']);
    Route::middleware('permission:create branches')->post('/branches', [BranchController::class, 'store']);
    Route::middleware('permission:view branches')->get('/branches/{branch}', [BranchController::class, 'show']);
    Route::middleware('permission:edit branches')->put('/branches/{branch}', [BranchController::class, 'update']);
    Route::middleware('permission:delete branches')->delete('/branches/{branch}', [BranchController::class, 'destroy']);
    Route::middleware('permission:view branches')->get('/branches/{branch}/employees', [BranchController::class, 'employees']);

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:view departments')->get('/departments', [DepartmentController::class, 'index']);
    Route::middleware('permission:create departments')->post('/departments', [DepartmentController::class, 'store']);
    Route::middleware('permission:view departments')->get('/departments/{department}', [DepartmentController::class, 'show']);
    Route::middleware('permission:edit departments')->put('/departments/{department}', [DepartmentController::class, 'update']);
    Route::middleware('permission:delete departments')->delete('/departments/{department}', [DepartmentController::class, 'destroy']);

    Route::middleware('permission:view departments')->get('/departments-hierarchy', [DepartmentController::class, 'hierarchy']);

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:view employees')->get('/employees', [EmployeeController::class, 'index']);
    Route::middleware('permission:create employees')->post('/employees', [EmployeeController::class, 'store']);
    Route::middleware('permission:view employees')->get('/employees/{employee}', [EmployeeController::class, 'show']);
    Route::middleware('permission:edit employees')->put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::middleware('permission:delete employees')->delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Document Categories
    |--------------------------------------------------------------------------
    */

    Route::apiResource('document-categories', DocumentCategoryController::class);

    /*
    |--------------------------------------------------------------------------
    | Document Prefixes
    |--------------------------------------------------------------------------
    */

    Route::apiResource('document-prefixes', DocumentPrefixController::class);

    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */

    Route::apiResource('documents', DocumentController::class);
    Route::post('/documents/{document}/publish', [DocumentController::class, 'publish']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('permission:view roles')->get('/roles', [RoleController::class, 'index']);
    Route::middleware('permission:create roles')->post('/roles', [RoleController::class, 'store']);
    Route::middleware('permission:view roles')->get('/roles/{role}', [RoleController::class, 'show']);
    Route::middleware('permission:edit roles')->put('/roles/{role}', [RoleController::class, 'update']);
    Route::middleware('permission:delete roles')->delete('/roles/{role}', [RoleController::class, 'destroy']);
    Route::middleware('permission:assign permissions')->post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);

    Route::get('permissions', [PermissionController::class, 'index']);
    Route::get('users/{user}/permissions', [PermissionController::class, 'getPermissions']);
});


