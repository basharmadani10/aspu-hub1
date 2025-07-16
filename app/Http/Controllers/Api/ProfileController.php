<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Subject;
use App\Models\User;
use App\Models\Post;
use App\Models\UserSemester;
use App\Models\UserSubject;
use App\Notifications\NewMessageNotification;
use Storage;
class ProfileController extends Controller
{

    public function getProfile(Request $request)
    {
        $user = $request->user();


        $user_semester = UserSemester::where('userID', $user->id)
                                   ->with('specialization')
                                   ->orderBy('semester_number', 'desc')
                                   ->first();


        $semesterMap = [
            1 => "first year", 2 => "first year",
            3 => "second year", 4 => "second year",
            5 => "third year", 6 => "third year",
            7 => "fourth year", 8 => "fourth year",
            9 => "fifth year", 10 => "fifth year"
        ];


        $imageUrl = $user->image ? asset('storage/' . $user->image) : null;


        $cumulativeHours = $this->calculateCompletedHours($user->id);


        $userSpecializationName = $user_semester->specialization->name ?? 'Unknown Specialization';
        $userYear = $semesterMap[$user_semester->semester_number ?? 0] ?? 'Unknown';

        return response()->json([
            'id' => $user->id,
            'f_name' => $user->first_name,
            'l_name' => $user->last_name,
            'bio' => $user->bio,
            'year' => $userYear,
            'major' => $userSpecializationName,
            'profile_image' => $imageUrl,
            'cumulative_hours' => $cumulativeHours,
        ]);
    }


    private function calculateCompletedHours($userId)
    {
        $hours = 0;
        $finishedSubjects = UserSubject::where('userID', $userId)
                                    ->where('has_been_finished', true)
                                    ->with(['subject' => function($query) {
                                        $query->select('id', 'hour_count');
                                    }])
                                    ->get();

        foreach ($finishedSubjects as $userSubject) {
            if ($userSubject->subject && $userSubject->subject->hour_count) {
                $hours += $userSubject->subject->hour_count;
            }
        }


        User::where('id', $userId)->update(['number_of_completed_hours' => $hours]);

        return $hours;
    }

    public function Add_new_subject(Request $data)
    {
        $user = $data->user();

        UserSubject::create([
            'userID' => $user->id,
            'subectID' => $data->subject_id,
            'has_been_finished' => $data->has_been_finished,
            'has_been_canceled' => $data->has_been_canceld,
            'mark' => $data->mark
        ]);


        $this->calculateCompletedHours($user->id);

        return response()->json(["message" => "subject Added successfully"], 200);
    }
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'string|max:255',
            'bio' => 'nullable|string|max:500',
            'year' => 'nullable|string|max:100',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $path = $file->store('profiles', 'public');
            $validated['profile_image'] = $path;
        }

        $user->update($validated);

        return response()->json([
            'message' => 'profile updated successfult',
            'user' => $user
        ]);
    }
    public function getUserPosts(Request $request)
    {
        $user = $request->user();

        $posts = Post::where('user_id', $user->id)
            ->with(['photos', 'user:id,first_name,last_name,image'])
            ->get()
            ->map(function ($post) {

                $post->photos->transform(function ($photo) {
                    $photo->photo_url = Storage::url($photo->photo);
                    return $photo;
                });

      
                $post->author_image = $post->user->image ? Storage::url($post->user->image) : null;

                return $post;
            });

        $profileImageUrl = $user->image ? Storage::url($user->image) : null;

        return response()->json([
            'username' => $user->first_name,
            'posts' => $posts,
            'profile_image' => $profileImageUrl
        ]);
    }

    public function getUserComments(Request $request)
    {
        $user = $request->user();

        $comments = Comment::where('user_id', $user->id)
            ->with(['childComments.user', 'parentComment.user', 'user'])
            ->with(['childComments', 'parentComment', 'post'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'comments' => $comments
        ]);
    }


    public function Get_user_subject(Request $data)
    {
        $user = $data->user();

        $subjects = $user->userSubjects()->with('subject')->get()->pluck('subject');

        return response()->json($subjects, 200);
    }




    public function uploadProfileImage(Request $request)

{

 $user = $request->user();



$validated = $request->validate([

'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
]);


if ($request->hasFile('profile_image')) {

$file = $request->file('profile_image');

$path = $file->store('profiles', 'public');

$user->image = $path;

$user->save();


return response()->json([
'message' => 'Image uploaded successfully',

'image_url' => asset('storage/' . $path)

]);

}



return response()->json(['error' => 'No image uploaded'], 400);

}


    public function Get_subject_info(Request $data)
    {
        $subject_id = $data->header('subject_id');
        $Subject = Subject::where('id', $subject_id)->first('Description');
        return response()->json($Subject, 200);
    }



}
