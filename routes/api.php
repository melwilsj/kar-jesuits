<?php

use App\Http\Controllers\Api\V1\JesuitController;
use App\Http\Controllers\Api\V1\ProvinceDataController;
use App\Http\Controllers\Api\V1\TimeTravelController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\Auth\FirebaseAuthController;
use App\Http\Controllers\Api\V1\CommissionController;
use App\Http\Controllers\Api\V1\InstitutionController;
use App\Http\Controllers\Api\V1\JesuitFilterController;
use App\Http\Controllers\Api\V1\SocietyDirectoryController;
use App\Http\Controllers\Api\V1\StatisticsController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is a cleaned-up version with only the routes currently being used.
| Commented sections contain routes that may be needed in the future.
*/

// V1 Routes
Route::prefix('v1')->group(function () {
    // Auth Routes - ACTIVELY USED
    Route::prefix('auth')->group(function () {
        Route::post('check-phone', [FirebaseAuthController::class, 'verifyPhoneNumber']);
        Route::post('phone/login', [FirebaseAuthController::class, 'phoneLogin']);
        Route::post('google/login', [FirebaseAuthController::class, 'googleLogin']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [FirebaseAuthController::class, 'logout']);
        });
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Core data routes - ACTIVELY USED
        Route::get('/province-jesuits', [ProvinceDataController::class, 'getProvinceJesuitsData']);
        Route::get('/province-communities', [ProvinceDataController::class, 'getProvinceCommunitiesData']);
        Route::get('/current-jesuit', [JesuitController::class, 'getCurrentJesuit']);
        Route::get('/user-details', function (Request $request) {
            return $request->user();
        });
        
        // Time travel routes - ACTIVELY USED
        Route::prefix('time-travel')->group(function () {
            Route::post('/state', [TimeTravelController::class, 'getStateAt']);
            Route::get('/history/{model_type}/{model_id}', [TimeTravelController::class, 'getModelHistory']);
        });

        // Testing route - ACTIVELY USED
        Route::get('/test-auth', function (Request $request) {
            return response()->json([
                'message' => 'Authenticated successfully',
                'user' => $request->user(),
                'token_abilities' => $request->user()->currentAccessToken()->abilities ?? [],
            ]);
        });

        // NEW PROVINCE DIRECTORY ROUTES
        
        // 1. Jesuit Filters
        Route::prefix('province/jesuits')->group(function () {
            Route::get('/formation', [JesuitFilterController::class, 'byFormation']);
            Route::get('/common-houses', [JesuitFilterController::class, 'inCommonHouses']);
            Route::get('/other-provinces', [JesuitFilterController::class, 'inOtherProvinces']);
            Route::get('/outside-india', [JesuitFilterController::class, 'outsideIndia']);
            Route::get('/other-residing', [JesuitFilterController::class, 'otherResiding']);
        });

        // 2. Institution Filters
        Route::prefix('province/institutions')->group(function () {
            Route::get('/', [InstitutionController::class, 'all']);
            Route::get('/educational', [InstitutionController::class, 'educational']);
            Route::get('/social-centers', [InstitutionController::class, 'socialCenters']);
            Route::get('/parishes', [InstitutionController::class, 'parishes']);
        });

        // 3. Commission Filters
        Route::prefix('province/commissions')->group(function () {
            Route::get('/', [CommissionController::class, 'index']);
            Route::get('/{code}', [CommissionController::class, 'byCode']);
        });

        // 4. Statistics
        Route::prefix('province/statistics')->group(function () {
            Route::get('/age-distribution', [StatisticsController::class, 'ageDistribution']);
            Route::get('/formation', [StatisticsController::class, 'formationStats']);
            Route::get('/geographical', [StatisticsController::class, 'geographicalDistribution']);
            Route::get('/ministry', [StatisticsController::class, 'ministryDistribution']);
            Route::get('/yearly-trends', [StatisticsController::class, 'yearlyTrends']);
        });

        // 5. Directory of Houses
        Route::prefix('society')->group(function () {
            Route::get('/assistancies', [SocietyDirectoryController::class, 'getAssistancies']);
        });
        Route::prefix('assistancy/{assistancy_id}')->group(function () {
            Route::get('/provinces', [SocietyDirectoryController::class, 'getProvincesByAssistancy']);
            Route::get('/regions', [SocietyDirectoryController::class, 'getRegionsByAssistancy']);
        });
        Route::get('/province/{code}/communities', [SocietyDirectoryController::class, 'getCommunitiesByProvince']);
        Route::get('/region/{code}/communities', [SocietyDirectoryController::class, 'getCommunitiesByRegion']);

        // Events
        Route::get('events/upcoming', [EventController::class, 'upcoming'])->name('events.upcoming');
        Route::get('events/past', [EventController::class, 'past'])->name('events.past');
        Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
        
        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

        // FCM token registration
        Route::post('fcm/register', [UserController::class, 'registerFcmToken'])->name('fcm.register');
        Route::post('fcm/unregister', [UserController::class, 'unregisterFcmToken'])->name('fcm.unregister');
    });
});

// V2 Routes (when needed)
Route::prefix('v2')->group(function () {
    // Future V2 routes
    Route::get('/test', function () {
        return response()->json([
            'message' => 'Hello from V2',
        ]);
    });
});

