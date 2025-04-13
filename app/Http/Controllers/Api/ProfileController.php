<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
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
        // $Semesternumber=$user_semester->semester_number;
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
        'message' => 'تم تحديث الملف الشخصي بنجاح',
        'user' => $user
    ]);
}
public function getUserPosts(Request $request)
{
    $user = $request->user();

    $posts = Post::where('user_id', $user->id)
                ->with(['user','comments.user'])->where('user_id',$user->id) 
                
                ->get();

    return response()->json([
        'posts' => $posts
    ]);
}
public function getUserComments(Request $request)
{
    $user = $request->user();

    $comments = Comment::where('user_id', $user->id)
                ->with(['childComments.user','parentComment.user','user']) 
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json([
        'comments' => $comments
    ]);
}
public function votePost(Request $request)
{
    $validated = $request->validate([
        'vote' => 'required|in:up,down'
    ]);

    $post = Post::findOrFail($request->postid);

    if ($validated['vote'] === 'up') {
        $post->positiveVotes += 1;
    } else {
        $post->negativeVotes += 1;
    }

    $post->save();

    return response()->json([
        'message' => 'تم التصويت بنجاح',
        'votes' => [
            'positive' => $post->positiveVotes,
            'negative' => $post->negativeVotes
        ]
    ]);
}
public function AddComment(Request $data){
    {
        $validated = $data->validate([
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);
    
        $comment = Comment::create([
            'content' => $validated['content'],
            'post_id' => $validated['post_id'],
            'user_id' => auth()->id(), // أو $data->user()->id لو عامل Auth::guard
            'parent_comment_id' => $validated['parent_comment_id'] ?? null,
            'positive_votes' => 0,
            'negative_votes' => 0,
        ]);
    
        return response()->json([
            'message' => 'تم إضافة التعليق بنجاح',
            'comment' => $comment
        ], 201);
    }
    
}
}
