<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentPrefixController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Public document verification (QR scan)
Route::get('/verify-document/{token}', 
    [DocumentController::class, 'verify']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum Required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/me', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'user' => $request->user(),
                'roles' => $request->user()->roles,
                'permissions' => $request->user()->permissions,
            ]);
        });
    });


    /*
    |--------------------------------------------------------------------------
    | ROLE & PERMISSION MANAGEMENT (Super Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Super Admin')->group(function () {

        Route::apiResource('roles', RoleController::class);

        Route::post('roles/{role}/permissions', 
            [RoleController::class, 'assignPermissions']);

        Route::post('users/{user}/assign-role', 
            [RoleController::class, 'assignRoleToUser']);

        Route::apiResource('permissions', PermissionController::class);

        Route::apiResource('organizations', OrganizationController::class);
    });


    /*
    |--------------------------------------------------------------------------
    | Branch Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:view branches')
        ->get('branches', [BranchController::class, 'index']);

    Route::middleware('permission:create branches')
        ->post('branches', [BranchController::class, 'store']);

    Route::middleware('permission:edit branches')
        ->put('branches/{branch}', [BranchController::class, 'update']);

    Route::middleware('permission:delete branches')
        ->delete('branches/{branch}', [BranchController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | Document Category Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:view document categories')
        ->get('document-categories', [DocumentCategoryController::class, 'index']);

    Route::middleware('permission:create document categories')
        ->post('document-categories', [DocumentCategoryController::class, 'store']);

    Route::middleware('permission:edit document categories')
        ->put('document-categories/{document_category}', [DocumentCategoryController::class, 'update']);

    Route::middleware('permission:delete document categories')
        ->delete('document-categories/{document_category}', [DocumentCategoryController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | Document Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:view documents')
        ->get('documents', [DocumentController::class, 'index']);

    Route::middleware('permission:create documents')
        ->post('documents', [DocumentController::class, 'store']);

    Route::middleware('permission:edit documents')
        ->put('documents/{document}', [DocumentController::class, 'update']);

    Route::middleware('permission:delete documents')
        ->delete('documents/{document}', [DocumentController::class, 'destroy']);

    Route::middleware('permission:view documents')
        ->get('documents/{document}', [DocumentController::class, 'show']);


    /*

    
    |--------------------------------------------------------------------------
    | Document Prefix Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:view document prefixes')
        ->get('document-prefixes', [DocumentPrefixController::class, 'index']);

    Route::middleware('permission:create document prefixes')
        ->post('document-prefixes', [DocumentPrefixController::class, 'store']);

    Route::middleware('permission:edit document prefixes')
        ->put('document-prefixes/{document_prefix}', [DocumentPrefixController::class, 'update']);

    Route::middleware('permission:delete document prefixes')
        ->delete('document-prefixes/{document_prefix}', [DocumentPrefixController::class, 'destroy']);

    Route::middleware('permission:view document prefixes')
        ->get('document-prefixes/{document_prefix}', [DocumentPrefixController::class, 'show']);

        
});