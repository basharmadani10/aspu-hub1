<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Photo;
use App\Models\Video;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;
class PostController extends Controller
{public function Addpost(Request $request)
    {
        // التحقق من البيانات
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'typePost' => 'required|string',
            'community_id' => 'required|exists:communities,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // إنشاء البوست
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'typePost' => $request->typePost,
            'community_id' => $request->community_id,
            'user_id' => auth()->id(), // أو $request->user_id حسب السياق
            'positiveVotes' => 0,
            'negativeVotes' => 0,
        ]);
    
        // رفع الصور وربطها بالبوست
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('posts', $imageName, 'public');
    
                Photo::create([
                    'post_id' => $post->id,
                    'photo' => $path,
                ]);
            }
        }
    
        return response()->json([
            'message' => 'تم إنشاء البوست بنجاح مع الصور',
            'post' => $post->load('photos')
        ], 201);
    }
   
    public function GetAllPost(Request $data){
        $user = $data->user();
        $user = User::with('Subscribe_Communities')->find($user->id);
        $community_ids = $user->Subscribe_Communities->pluck('community_id');
        $posts_comment = Post::whereIn('community_id', $community_ids)
            ->with(['user', 'comments.User']) // post author + comment authors
            ->get();
        $user_post_comment = Post::where('user_id', $user->id)
            ->with(['user', 'comments.User'])
            ->get();
        return response()->json([
            'post' => $posts_comment,
            'user_post' => $user_post_comment
        ], 200);
    }
    public function AddImage(Request $data){
        $rules = [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        // Create validator instance
        $validator = Validator::make($request->all(), $rules);
        // Check if validation fails
        if ($validator->fails()) {
             return response()->json(["message"=>"the file is very larg"], 401);
        }
        // Proceed with file upload if validation passes
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads'), $imageName);
        $imagePath = asset('uploads/' . $imageName);
        $photo= new Photo();
        
        return response()->json(['image_url' => $imagePath]);
    }
   
}
