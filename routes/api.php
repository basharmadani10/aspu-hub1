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
use App\Http\Controllers\Api\UserSubjectController; // Ensure this is imported
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\CommunitySubscriptionController;
use App\Http\Controllers\API\RoadmapController;

use App\Http\Controllers\Api\SpecializationRoadmapController;
Route::get('/roadmaps', [RoadmapController::class, 'getRoadmaps']);

Route::get('/specialization-roadmaps', [SpecializationRoadmapController::class, 'getSpecializationRoadmaps']);

Route::get('/user', function (Request $request) {
    return $request->user();
});

///////////////////////////////////////Auth/////////////////////////
Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/verify-email', [RegistrationController::class, 'verifyEmail']);

/////////////////////////////////////////student////////////////////
Route::prefix('student')->group(function () {
    // Public student routes that DO NOT require authentication
    Route::post('/login', [StudentAuthController::class, 'login'])->name('login');
    Route::post('/password/send-code', [PasswordResetCodeController::class, 'sendResetCode'])->name('password.send.code');
    Route::post('/password/reset', [PasswordResetCodeController::class, 'verifyCodeAndResetPassword'])->name('password.reset.submit');

    // ✅ Protected Student Routes with Block Check
    Route::middleware(['auth:sanctum', 'blocked'])->group(function () {
        Route::post('/logout', [StudentAuthController::class, 'logout']);
        Route::get('/dashboard', [StudentAuthController::class, 'dashboard']);




        //////////////////////////////Community_post///////////////////////////////
        Route::get('post/get',[CummunityController::class,'GetAllPost'] );
        Route::get('/post/general', [CummunityController::class, 'allPostGeneral']);
        Route::get('/post/software', [CummunityController::class, 'allPostSoftware']);
        Route::get('/post/Network', [CummunityController::class, 'allPostNetwork']);
        Route::post('post/Add',[PostController::class,'Addpost'] );
        Route::post('post/{post}/update', [PostController::class, 'updatePost']);
        Route::delete('post/{post}/delete', [PostController::class, 'deletePost']);
        Route::post('VotePost', [PostController::class,'votePost']);
        Route::get('/posts/by-tag/{tag}', [PostController::class, 'getPostsByTag']);
        Route::post('posts/{post}/report', [PostController::class, 'report'])->name('posts.report');
        Route::get('/tags', [TagController::class, 'index']);
        Route::post('/tags', [TagController::class, 'store']);
        Route::post('Get_Comuuinty_post', [CummunityController::class,'Get_All_Post']);

        //////////////////////profile//////////////////////////////////////////////
        Route::get('/userpost', [ProfileController::class,'getUserPosts']);
        Route::post('/profile/upload-image', [ProfileController::class, 'uploadProfileImage']);
        Route::get('Get_user_subject', [ProfileController::class,'Get_user_subject']);
        Route::post('Add_new_subject', [ProfileController::class,'Add_new_subject']); // This might be redundant with registerSubjectsForSemester
        Route::get('Get_subject_info', [ProfileController::class,'Get_subject_info']);
        Route::get('Getuserinfo', [ProfileController::class,'getProfile']);
        Route::get('Getusercomment', [ProfileController::class,'getUserComments']);

        ///////////////////////////////////////////////////////////////////////
        Route::post('/communities/subscribe', [CommunitySubscriptionController::class, 'subscribe']);
        Route::post('AddComment', [CommentController::class, 'addComment']);
        Route::post('VoteComment', [CommentController::class, 'voteComment']);
        Route::post('comment/{comment}/update', [CommentController::class, 'updateComment']);
        Route::delete('comment/{comment}/delete', [CommentController::class, 'deleteComment']);
        Route::post('comment/{comment}/report', [CommentController::class, 'report']);

        ///////////////////notifications///////////////////////////////////
        Route::post('create_notification', [NotificationController::class,'create_notification']);
        Route::get('get_all_notification', [NotificationController::class,'get_all_notification']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);

        ///////////////////subjects/////////////////////////////////////////
        Route::get('/subjects/Unfinished-Subjects', [UserSubjectController::class, 'getUnfinishedSubjects']);
        Route::get('/user/subjects/current-semester', [UserSubjectController::class, 'getCurrentSemesterSubjects']);
        Route::get('/subjects/completed-subject', [UserSubjectController::class, 'getCompletedSubjects']);

        // --- NEW/UPDATED ROUTES FOR SUBJECT REGISTRATION AND DOCUMENTS ---
        Route::get('/subjects/all-for-setup', [UserSubjectController::class, 'getAllSubjectsForInitialSetup']);
        Route::post('/subjects/submit-initial', [UserSubjectController::class, 'submitInitialCompletedSubjects']); // Initial setup
        Route::post('/subjects/register-for-semester', [UserSubjectController::class, 'registerSubjectsForSemester']); // New semester registration

        Route::get('/subjects/registration-status', [UserSubjectController::class, 'checkRegistrationStatus']); // New status check endpoint

        Route::post('/subjects/complete', [UserSubjectController::class, 'completeSubjects']);
        Route::post('/specialization/change', [UserSubjectController::class, 'changeSpecialization']);
        Route::get('/subjects/available', [UserSubjectController::class, 'getAvailableSubjects']); // Ensure this method exists if used

        Route::get('/subjects/{subjectId}/lectures', [UserSubjectController::class, 'getSubjectLectures']);
        Route::get('/subjects/{subjectId}/summaries', [UserSubjectController::class, 'getSubjectSummaries']);


 Route::get('/{subjectId}/documents', [UserSubjectController::class, 'getSubjectDocuments']); // This maps to /api/student/{subjectId}/documents
    });
});
