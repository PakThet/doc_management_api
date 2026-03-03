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

Route::get('/documents/verify/{token}', [DocumentController::class, 'verify']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:view users')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::get('/users/{user}/roles', [UserController::class, 'getRoles']);
        Route::get('/users/{user}/permissions', [UserController::class, 'getPermissions']);
    });

    Route::middleware('permission:create users')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:edit users')->put('/users/{user}', [UserController::class, 'update']);
    Route::middleware('permission:delete users')->delete('/users/{user}', [UserController::class, 'destroy']);

    Route::middleware('permission:update users')->post('/users/{user}/roles', [UserController::class, 'assignRoles']);

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

    Route::middleware('permission:view organizations')->get('/organizations/{organization}/statistics', [OrganizationController::class, 'statistics']);

    /*
    |--------------------------------------------------------------------------
    | Branches
    |--------------------------------------------------------------------------
    */
    Route::middleware('')->get('/branches', [BranchController::class, 'index']);
    Route::middleware('')->post('/branches', [BranchController::class, 'store']);
    Route::middleware('')->get('/branches/{branch}', [BranchController::class, 'show']);
    Route::middleware('')->put('/branches/{branch}', [BranchController::class, 'update']);
    Route::middleware('')->delete('/branches/{branch}', [BranchController::class, 'destroy']);

    Route::middleware('')->get('/branches/{branch}/employees', [BranchController::class, 'employees']);

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */
    Route::middleware('')->get('/departments', [DepartmentController::class, 'index']);
    Route::middleware('')->post('/departments', [DepartmentController::class, 'store']);
    Route::middleware('')->get('/departments/{department}', [DepartmentController::class, 'show']);
    Route::middleware('')->put('/departments/{department}', [DepartmentController::class, 'update']);
    Route::middleware('')->delete('/departments/{department}', [DepartmentController::class, 'destroy']);

    Route::middleware('')->get('/departments-hierarchy', [DepartmentController::class, 'hierarchy']);
    Route::middleware('')->get('/departments/{department}/employees', [DepartmentController::class, 'employees']);

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */
    Route::middleware('')->get('/employees', [EmployeeController::class, 'index']);
    Route::middleware('')->post('/employees', [EmployeeController::class, 'store']);
    Route::middleware('')->get('/employees/{employee}', [EmployeeController::class, 'show']);
    Route::middleware('')->put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::middleware('')->delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

    Route::middleware('')->post('/employees/{employee}/link-user', [EmployeeController::class, 'linkUser']);
    Route::middleware('')->delete('/employees/{employee}/unlink-user', [EmployeeController::class, 'unlinkUser']);

    Route::middleware('')->get('/employees-statistics', [EmployeeController::class, 'statistics']);

    /*
    |--------------------------------------------------------------------------
    | Document Categories
    |--------------------------------------------------------------------------
    */
    Route::apiResource('document-categories', DocumentCategoryController::class)
        ->middleware('');

    /*
    |--------------------------------------------------------------------------
    | Document Prefixes
    |--------------------------------------------------------------------------
    */
    Route::middleware('')->get('/document-prefixes', [DocumentPrefixController::class, 'index']);
    Route::middleware('')->post('/document-prefixes', [DocumentPrefixController::class, 'store']);
    Route::middleware('')->get('/document-prefixes/{documentPrefix}', [DocumentPrefixController::class, 'show']);
    Route::middleware('')->put('/document-prefixes/{documentPrefix}', [DocumentPrefixController::class, 'update']);
    Route::middleware('')->delete('/document-prefixes/{documentPrefix}', [DocumentPrefixController::class, 'destroy']);

    Route::middleware('')->post('/document-prefixes/{documentPrefix}/set-default', [DocumentPrefixController::class, 'setDefault']);
    Route::middleware('')->get('/document-prefixes/{documentPrefix}/generate-next-code', [DocumentPrefixController::class, 'generateNextCode']);

    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */
    Route::middleware('')->get('/documents', [DocumentController::class, 'index']);
    Route::middleware('')->post('/documents', [DocumentController::class, 'store']);
    Route::middleware('')->get('/documents/{document}', [DocumentController::class, 'show']);
    Route::middleware('')->put('/documents/{document}', [DocumentController::class, 'update']);
    Route::middleware('')->delete('/documents/{document}', [DocumentController::class, 'destroy']);

    Route::middleware('')->get('/documents/{document}/download', [DocumentController::class, 'download']);
    Route::middleware('')->post('/documents/{document}/publish', [DocumentController::class, 'publish']);
    Route::middleware('')->post('/documents/{document}/archive', [DocumentController::class, 'archive']);
    Route::middleware('')->get('/documents-statistics', [DocumentController::class, 'statistics']);

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    */
    Route::middleware('')->get('/roles', [RoleController::class, 'index']);
    Route::middleware('')->post('/roles', [RoleController::class, 'store']);
    Route::middleware('')->get('/roles/{role}', [RoleController::class, 'show']);
    Route::middleware('')->put('/roles/{role}', [RoleController::class, 'update']);
    Route::middleware('')->delete('/roles/{role}', [RoleController::class, 'destroy']);

    Route::middleware('')->post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);

    Route::middleware('')->get('/permissions', [PermissionController::class, 'index']);
    Route::middleware('')->get('/permission-groups', [PermissionController::class, 'groups']);
});