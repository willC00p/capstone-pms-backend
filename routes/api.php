<?php

use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\TreeController;

// Add missing route for byLayout
Route::get('parking-assignments/by-layout/{layoutId}', [App\Http\Controllers\API\ParkingAssignmentController::class, 'byLayout']);
use App\Http\Controllers\API\ParkingLayoutController;
use App\Http\Controllers\API\ParkingAssignmentController;
use App\Http\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes for serving images
Route::get('image/{path}', [FileController::class, 'serveImage'])->where('path', '.*');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout');
});

// Forgot password (code-based)
Route::post('forgot/send-code', [App\Http\Controllers\API\ForgotPasswordController::class, 'sendResetCode']);
Route::post('forgot/reset', [App\Http\Controllers\API\ForgotPasswordController::class, 'resetPassword']);

// Frontend-friendly aliases
Route::post('forgot-password', [App\Http\Controllers\API\ForgotPasswordController::class, 'sendResetCode']);
Route::post('reset-password', [App\Http\Controllers\API\ForgotPasswordController::class, 'resetPassword']);

// Account routes (frontend expects /account/*)
Route::get('account', [App\Http\Controllers\API\SettingsController::class, 'profile']);
Route::post('account/update-profile', [App\Http\Controllers\API\SettingsController::class, 'updateProfile']);
Route::post('account/profile-pic', [App\Http\Controllers\API\SettingsController::class, 'updateProfilePic']);
// Alias to match backend2
Route::post('account/update-profile-pic', [App\Http\Controllers\API\SettingsController::class, 'updateProfilePic']);
Route::post('account/password', [App\Http\Controllers\API\SettingsController::class, 'updatePassword']);
Route::delete('account/delete', [App\Http\Controllers\API\SettingsController::class, 'deleteAccount']);
     
Route::middleware('auth:sanctum')->group( function () {
    // Settings and profile routes
    Route::get('settings/profile', [App\Http\Controllers\API\SettingsController::class, 'profile']);
    Route::put('settings/profile', [App\Http\Controllers\API\SettingsController::class, 'updateProfile']);
    Route::post('settings/profile-pic', [App\Http\Controllers\API\SettingsController::class, 'updateProfilePic']);
    Route::put('settings/password', [App\Http\Controllers\API\SettingsController::class, 'updatePassword']);
    Route::delete('settings', [App\Http\Controllers\API\SettingsController::class, 'deleteAccount']);
    Route::resource('users', UsersController::class);
    Route::resource('products', ProductController::class);
    Route::resource('transactions', TransactionController::class);
    Route::resource('stores', StoreController::class);
    Route::prefix('parking-layouts')->group(function() {
        Route::get('/', [ParkingLayoutController::class, 'index']);
        Route::post('/', [ParkingLayoutController::class, 'store']);
        Route::get('/{id}', [ParkingLayoutController::class, 'show']);
        Route::put('/{id}', [ParkingLayoutController::class, 'update']);
        Route::delete('/{id}', [ParkingLayoutController::class, 'destroy']);
    });
    
    // Parking Assignments
    Route::prefix('parking-assignments')->group(function() {
        Route::get('active', [ParkingAssignmentController::class, 'active']);
        Route::post('{assignment}/switch-parking', [ParkingAssignmentController::class, 'switchParking']);
        Route::post('{assignment}/end', [ParkingAssignmentController::class, 'endAssignment']);
    });
    // Place this route outside the prefix group to avoid resource route conflicts
    Route::get('parking-assignments/by-layout/{layoutId}', [ParkingAssignmentController::class, 'byLayout']);
    Route::resource('parking-assignments', ParkingAssignmentController::class);
    // Return users along with their user_details and registered vehicles for frontend autoload
    Route::get('users-with-vehicles', [App\Http\Controllers\API\UsersController::class, 'usersWithVehicles']);
    
    // Route::resource('trees', TreeController::class);
    Route::get('/trees', [TreeController::class, 'index']);
    Route::get('/trees/{lead}', [TreeController::class, 'show']);

    // Admin account creation endpoints (require admin role)
    Route::middleware('is_admin')->group(function() {
        Route::post('admin/create-student', [App\Http\Controllers\API\AdminController::class, 'createStudent']);
        Route::post('admin/create-faculty', [App\Http\Controllers\API\AdminController::class, 'createFaculty']);
        Route::post('admin/create-employee', [App\Http\Controllers\API\AdminController::class, 'createEmployee']);
        Route::post('admin/create-guard', [App\Http\Controllers\API\AdminController::class, 'createGuard']);
    });

    // Vehicles
    Route::get('vehicles', [App\Http\Controllers\API\VehiclesController::class, 'index']);
    Route::post('vehicles', [App\Http\Controllers\API\VehiclesController::class, 'store']);
    Route::get('vehicles/{vehicle}', [App\Http\Controllers\API\VehiclesController::class, 'show']);
    Route::put('vehicles/{vehicle}', [App\Http\Controllers\API\VehiclesController::class, 'update']);
    Route::delete('vehicles/{vehicle}', [App\Http\Controllers\API\VehiclesController::class, 'destroy']);
});
