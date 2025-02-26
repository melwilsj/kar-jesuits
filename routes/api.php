<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\BulkOperationsController;
use App\Http\Controllers\Api\{
    JesuitProfileController,
    FormationController,
    RoleAssignmentController,
    DocumentController,
    ExternalAssignmentController,
    ProvinceTransferController,
    TimeTravelController
};
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Legacy auth endpoints (to be deprecated)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/phone/verify', [AuthController::class, 'verifyPhone']);

// Public Firebase Authentication endpoints
Route::prefix('auth')->group(function () {
    Route::post('/verify-token', [FirebaseAuthController::class, 'verifyToken']);
    Route::post('/verify-phone', [FirebaseAuthController::class, 'verifyPhoneNumber']);
    Route::post('/phone/login', [FirebaseAuthController::class, 'verifyToken']);
    Route::post('/google/login', [FirebaseAuthController::class, 'verifyToken']);
});

// Protected routes with Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [FirebaseAuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // User management routes
    Route::apiResource('users', UserController::class);
    
    // Province routes
    Route::middleware('role:superadmin,province_admin')->group(function () {
        Route::apiResource('provinces', ProvinceController::class);
    });

    // Region routes
    Route::middleware('role:superadmin,province_admin,region_admin')->group(function () {
        Route::apiResource('regions', RegionController::class);
    });

    // Community routes
    Route::middleware('role:superadmin,province_admin,region_admin,community_superior')->group(function () {
        Route::apiResource('communities', CommunityController::class);
    });

    // Institution routes
    Route::middleware('role:superadmin,province_admin,region_admin,community_superior')->group(function () {
        Route::apiResource('institutions', InstitutionController::class);
    });

    // Commission routes
    Route::middleware('role:superadmin,province_admin,commission_head')->group(function () {
        Route::apiResource('commissions', CommissionController::class);
        Route::post('commissions/{commission}/members', [CommissionController::class, 'addMember']);
        Route::delete('commissions/{commission}/members', [CommissionController::class, 'removeMember']);
    });

    // Group routes
    Route::middleware('role:superadmin,province_admin')->group(function () {
        Route::apiResource('groups', GroupController::class);
        Route::post('groups/{group}/members', [GroupController::class, 'addMember']);
        Route::delete('groups/{group}/members', [GroupController::class, 'removeMember']);
    });

    // Public routes for authenticated users
    Route::get('provinces/{province}/public', [ProvinceController::class, 'showPublic']);
    Route::get('regions/{region}/public', [RegionController::class, 'showPublic']);
    Route::get('communities/{community}/public', [CommunityController::class, 'showPublic']);
    Route::get('institutions/{institution}/public', [InstitutionController::class, 'showPublic']);

    // Search routes
    Route::get('/search', [SearchController::class, 'search']);
    
    // Dashboard routes
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
    
    // Bulk operations routes
    Route::middleware('superadmin')->group(function () {
        Route::post('/bulk/delete', [BulkOperationsController::class, 'bulkDelete']);
    });

    // Jesuit Profile Management
    Route::get('jesuits/{user}/profile', [JesuitProfileController::class, 'show']);
    Route::post('jesuits/{user}/profile', [JesuitProfileController::class, 'update']);
    
    // Formation Management
    Route::get('jesuits/{user}/formation-history', [FormationController::class, 'formationHistory']);
    Route::post('jesuits/{user}/formation', [FormationController::class, 'updateStage']);
    
    // Role Assignments
    Route::post('jesuits/{user}/roles', [RoleAssignmentController::class, 'assign']);
    Route::get('jesuits/{user}/roles', [RoleAssignmentController::class, 'history']);
    Route::patch('role-assignments/{assignment}', [RoleAssignmentController::class, 'endAssignment']);
    
    // Document Management
    Route::post('jesuits/{user}/documents', [DocumentController::class, 'store']);
    Route::delete('documents/{document}', [DocumentController::class, 'destroy']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    
    // External Assignments
    Route::post('jesuits/{user}/external-assignments', [ExternalAssignmentController::class, 'assign']);
    Route::get('jesuits/{user}/external-assignments', [ExternalAssignmentController::class, 'history']);
    
    // Province Transfers
    Route::post('jesuits/{user}/transfer-request', [ProvinceTransferController::class, 'request']);
    Route::patch('transfers/{transfer}', [ProvinceTransferController::class, 'updateStatus']);

    // Time travel routes
    Route::prefix('time-travel')->group(function () {
        Route::post('/state', [TimeTravelController::class, 'getStateAt']);
        Route::get('/history/{model_type}/{model_id}', [TimeTravelController::class, 'getModelHistory']);
    });
    
    // User profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
});

// V1 API (Versioned API with consistent Firebase auth)
Route::prefix('v1')->group(function () {
    // Public auth endpoints
    Route::post('auth/phone/login', [FirebaseAuthController::class, 'phoneLogin']);
    Route::post('auth/google/login', [FirebaseAuthController::class, 'googleLogin']);
    
    // Protected routes
    Route::middleware(['auth:sanctum', 'auth.firebase'])->group(function () {
        Route::post('auth/logout', [FirebaseAuthController::class, 'logout']);
        
        // User profile
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        
        // Other protected routes can be added here
    });
});

// Firebase Auth routes for mobile app
Route::prefix('auth')->group(function () {
    Route::post('/phone-login', [MobileAuthController::class, 'phoneLogin']);
    Route::post('/google-login', [MobileAuthController::class, 'googleLogin']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [MobileAuthController::class, 'getUser']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
});