<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\SupervisorRegisterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\CommunityPostController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\RoadmapController;
use App\Http\Controllers\Admin\CommunityMemberController;

use App\Http\Controllers\Admin\AdminProfileController;



use App\Http\Controllers\Admin\AdminDocsController;

use App\Http\Controllers\Admin\SpecializationController;

Route::get('/', function () {
    return redirect()->route('supervisor.register.create');
});

// Admin/Supervisor Login Routes
Route::get('admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminLoginController::class, 'login']);

// Supervisor Registration Application Routes
Route::get('supervisor/register', [SupervisorRegisterController::class, 'showRegistrationForm'])->name('supervisor.register.create');
Route::post('supervisor/register', [SupervisorRegisterController::class, 'register'])->name('supervisor.register.store');

// Thank You Page Route (after application submission)
Route::view('/register/thank-you', 'auth.thank-you')->name('register.thankyou');


// =========================================================================
// == Authenticated & Admin Protected Routes
// =========================================================================

// This group requires the user to be logged in ('auth') and have admin privileges ('admin' middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {





    Route::get('/profile', [App\Http\Controllers\Admin\AdminProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('profile.update-password');
    // Admin Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Logout
    Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');

    // Community Management
    Route::get('communities', [CommunityController::class, 'index'])->name('communities.index');

    // Subject Management
    Route::resource('subjects', SubjectController::class)->except(['show']);

    Route::resource('docs', AdminDocsController::class)->except(['show', 'edit', 'update']);
    Route::resource('roadmaps', RoadmapController::class);


    Route::resource('specializations', SpecializationController::class);

    Route::resource('communities', CommunityController::class);
    Route::get('communities/{community}/members', [\App\Http\Controllers\Admin\CommunityMemberController::class, 'index'])
    ->name('communities.members.index');







    Route::resource('communities.posts', CommunityPostController::class)->only(['index', 'destroy']);

        // NEW: Document Management Routes

    // Tag Management
    Route::resource('tags', TagController::class);

    // Reports Management
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::post('/{report}/resolve', [ReportController::class, 'resolve'])->name('resolve');
        Route::delete('/{report}/delete-content', [ReportController::class, 'deleteContent'])->name('deleteContent');
        Route::delete('/{report}/ban-user', [ReportController::class, 'banUser'])->name('banUser');
    });

    // You can add the application review routes here in the future
    // For example:
    // Route::get('applications', [App\Http\Controllers\Admin\ApplicationController::class, 'index'])->name('applications.index');
    // Route::post('applications/{application}/approve', [App\Http\Controllers\Admin\ApplicationController::class, 'approve'])->name('applications.approve');


    Route::get('users/{user}', [CommunityMemberController::class, 'showUserInfo'])->name('users.show');
    Route::put('users/{user}/toggle-block', [CommunityMemberController::class, 'toggleBlock'])->name('users.toggleBlock');

});
