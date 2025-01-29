<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\SuperAdminAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\PasswordResetCodeController;
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




Route::post('/register', [RegistrationController::class, 'register']);

Route::get('/user', function (Request $request) {
    return "request->user()";
});


Route::prefix('student')->group(function () {
    Route::post('/login', [StudentAuthController::class, 'login'])->name('login');


    Route::middleware('auth:student')->group(function () {
        Route::post('/logout', [StudentAuthController::class, 'logout']);
        Route::get('/dashboard', [StudentAuthController::class, 'dashboard']);


Route::post('/password/send-code', [PasswordResetCodeController::class, 'sendResetCode'])
    ->name('password.send.code');
Route::post('/password/reset', [PasswordResetCodeController::class, 'verifyCodeAndResetPassword'])
    ->name('password.reset.submit');

    });
});




Route::get('/verify-email', [VerificationController::class, 'verify']);

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:admins')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);


        Route::post('/email/resend',[[RegistrationController::class,'resend']]);
     });
});

// Super Admin Routes
Route::prefix('super-admin')->group(function () {
    Route::post('/login', [SuperAdminAuthController::class, 'login']);
    Route::middleware('auth:superAdmins')->group(function () {
        Route::post('/logout', [SuperAdminAuthController::class, 'logout']);

        Route::post('/email/resend',[[RegistrationController::class,'resend']]);
     });
});
