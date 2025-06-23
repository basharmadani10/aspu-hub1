<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Subject;
use App\Models\User;
use App\Models\Post;
use App\Models\UserSubject;
use App\Notifications\NewMessageNotification;
class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $usersemester=[1=>"first year",2=>'first year',3=>
        'second year'
        ,4=>"second year"
        ,5=>'therd year',6=>'therd year'
        ,7=>'fourth year',8=>'fourth year',9=>'fifth year',10=>'fifth year'];
        $user = $request->user();
        $user_semester=User::join('user_semesters', 'users.id', '=', 'user_semesters.userID')
        ->select('semester_number')
        ->first();

        return response()->json([
            'id' => $user->id,
            'name' => $user->first_name,
            'bio' => $user->bio,
            'year' => $usersemester[$user_semester->semester_number],
            'profile_image' => $user->profile_image,

        ]);
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
                   ->with('photos')
                   ->get();

    $username = $user->first_name;

    return response()->json([
        'username' => $username,
        'posts'    => $posts
    ]);
}
public function getUserComments(Request $request)
{
    $user = $request->user();

    $comments = Comment::where('user_id', $user->id)

                ->with(['childComments.user','parentComment.user','user'])

                ->with(['childComments','parentComment','post'])

                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json([
        'comments' => $comments
    ]);
}


public function Get_user_subject(Request $data) {
    $user = $data->user();

    $subjects = $user->userSubjects()->with('subject')->get()->pluck('subject');

    return response()->json($subjects, 200);
}
public function Add_new_subject(Request $data){
$user=$data->user();
$subject_id=$data->subject_id;
UserSubject::create([
    'userID' => $user->id,
    'subectID' => $data->subject_id,
    'has_been_finished' => $data->has_been_finished,
    'has_been_canceled' => $data->has_been_canceld,
    'mark' =>$data->mark 

]);
return response()->json(["message"=>"subject Added succefully"], 200);
}
public function Get_subject_info(Request $data){
    $subject_id=$data->header('subject_id');
    $Subject=Subject::where('id',$subject_id)->first('Description');
    return response()->json($Subject, 200);
}
}
