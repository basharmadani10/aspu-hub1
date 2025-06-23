<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\SuperAdminAuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\CummunityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\PasswordResetCodeController;
use App\Http\Controllers\Api\UserSubjectController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\CommunitySubscriptionController;

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/verify-email', [RegistrationController::class, 'verifyEmail']);

Route::prefix('student')->group(function () {
    // Public student routes that DO NOT require authentication
    Route::post('/login', [StudentAuthController::class, 'login'])->name('login');
    Route::post('/password/send-code', [PasswordResetCodeController::class, 'sendResetCode'])->name('password.send.code');
    Route::post('/password/reset', [PasswordResetCodeController::class, 'verifyCodeAndResetPassword'])->name('password.reset.submit');

    // ✅ Protected Student Routes with Block Check
    // All routes inside this group require login AND will check if the user is blocked.
    Route::middleware(['auth:sanctum', 'blocked'])->group(function () {
        Route::post('/logout', [StudentAuthController::class, 'logout']);
        Route::get('/dashboard', [StudentAuthController::class, 'dashboard']);

        // Post Routes
        Route::get('post/get',[CummunityController::class,'GetAllPost'] );
        Route::get('/post/general', [CummunityController::class, 'allPostGeneral']);
        Route::get('/post/software', [CummunityController::class, 'allPostSoftware']);
        Route::get('/post/Network', [CummunityController::class, 'allPostNetwork']);
        Route::get('/userpost', [ProfileController::class,'getUserPosts']);
        Route::post('post/Add',[PostController::class,'Addpost'] );
        Route::post('post/{post}/update', [PostController::class, 'updatePost']);
        Route::delete('post/{post}/delete', [PostController::class, 'deletePost']);
        Route::post('VotePost', [PostController::class,'votePost']);
        Route::get('/posts/by-tag/{tag}', [PostController::class, 'getPostsByTag']);
        Route::post('posts/{post}/report', [PostController::class, 'report'])->name('posts.report');




        Route::get('/tags', [TagController::class, 'index']);
        Route::post('/tags', [TagController::class, 'store']);


        Route::post('Get_Comuuinty_post', [CummunityController::class,'Get_All_Post']);

        Route::get('Get_user_subject', [ProfileController::class,'Get_user_subject']);
        Route::post('Add_new_subject', [ProfileController::class,'Add_new_subject']);
        Route::get('Get_subject_info', [ProfileController::class,'Get_subject_info']);
        Route::get('Getuserinfo', [ProfileController::class,'getProfile']);
        Route::get('Getusercomment', [ProfileController::class,'getUserComments']);



        Route::post('/communities/subscribe', [CommunitySubscriptionController::class, 'subscribe']);

        Route::post('AddComment', [CommentController::class, 'addComment']);
        Route::post('VoteComment', [CommentController::class, 'voteComment']);
        Route::post('comment/{comment}/update', [CommentController::class, 'updateComment']);
        Route::delete('comment/{comment}/delete', [CommentController::class, 'deleteComment']);

        Route::post('comment/{comment}/report', [CommentController::class, 'report']);



        Route::post('create_notification', [NotificationController::class,'create_notification']);
        Route::get('get_all_notification', [NotificationController::class,'get_all_notification']);


        Route::get('/subjects/all-for-setup', [UserSubjectController::class, 'getAllSubjectsForInitialSetup']);
        Route::post('/subjects/submit-initial', [UserSubjectController::class, 'submitInitialCompletedSubjects']);
        Route::post('/subjects/register-for-semester', [UserSubjectController::class, 'registerSubjectsForSemester']);
        Route::post('/subjects/complete', [UserSubjectController::class, 'completeSubjects']);
        Route::post('/specialization/change', [UserSubjectController::class, 'changeSpecialization']);



Route::post('/subjects/confirm-completed', [UserSubjectController::class, 'confirmCompletedSubjects']);

Route::get('/subjects/available', [UserSubjectController::class, 'getAvailableSubjects']);


    });
});



Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:admins')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/email/resend',[[RegistrationController::class,'resend']]);
   });
});


Route::prefix('super-admin')->group(function () {
    Route::post('/login', [SuperAdminAuthController::class, 'login']);
    Route::middleware('auth:superAdmins')->group(function () {
        Route::post('/logout', [SuperAdminAuthController::class, 'logout']);
        Route::post('/email/resend',[[RegistrationController::class,'resend']]);
   });
});
