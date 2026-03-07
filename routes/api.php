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

Route::get('/documents/verify/{token}', [DocumentController::class, 'verify'])
    ->name('documents.verify');

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('organizations', OrganizationController::class)
        ->except(['create', 'edit']);

    Route::apiResource('branches', BranchController::class)
        ->except(['create', 'edit'])
        ->middlewareFor(['index', 'show'], 'permission:branches.view')
        ->middlewareFor('store', 'permission:branches.create')
        ->middlewareFor('update', 'permission:branches.update')
        ->middlewareFor('destroy', 'permission:branches.delete');

    Route::apiResource('departments', DepartmentController::class)
        ->except(['create', 'edit'])
        ->middlewareFor(['index', 'show'], 'permission:departments.view')
        ->middlewareFor('store', 'permission:departments.create')
        ->middlewareFor('update', 'permission:departments.update')
        ->middlewareFor('destroy', 'permission:departments.delete');

    Route::apiResource('employees', EmployeeController::class)
        ->except(['create', 'edit'])
        ->middlewareFor(['index', 'show'], 'permission:employees.view')
        ->middlewareFor('store', 'permission:employees.create')
        ->middlewareFor('update', 'permission:employees.update')
        ->middlewareFor('destroy', 'permission:employees.delete');

    Route::apiResource('document-categories', DocumentCategoryController::class)
        ->parameters(['document-categories' => 'documentCategory'])
        ->except(['create', 'edit']);
    Route::apiResource('document-prefixes', DocumentPrefixController::class)
        ->parameters(['document-prefixes' => 'documentPrefix'])
        ->except(['create', 'edit']);

    Route::apiResource('documents', DocumentController::class)
        ->except(['create', 'edit'])
        ->middlewareFor(['index', 'show'], 'permission:documents.view')
        ->middlewareFor('store', 'permission:documents.create')
        ->middlewareFor('update', 'permission:documents.update')
        ->middlewareFor('destroy', 'permission:documents.delete');

    Route::apiResource('users', UserController::class)
        ->except(['create', 'edit'])
        ->middlewareFor(['index', 'show'], 'permission:users.view')
        ->middlewareFor('store', 'permission:users.create')
        ->middlewareFor('update', 'permission:users.update')
        ->middlewareFor('destroy', 'permission:users.delete');

    Route::apiResource('roles', RoleController::class)
        ->except(['create', 'edit'])
        ->middleware('permission:roles.manage');

    Route::apiResource('permissions', PermissionController::class)
        ->except(['create', 'edit'])
        ->middleware('permission:permissions.manage');

    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions'])
        ->middleware('permission:roles.manage');
});
