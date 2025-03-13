<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Photo;
use App\Models\Video;

class PostController extends Controller
{
    public function AddNewPost(Request $data){
        $rules = [
            'title'         => 'required|string|max:255',
            'content'       => 'required|string',
            'typePost'      => 'required|in:Ask,Advise,Story',
            
           
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator], 400);
        }
        $usertoken = $data->header('token');
        $user=User::where('email_verification_token',$usertoken)->first();
        $user_id=$user->id;
        $community_id=$data->community_id;
        $post= new Post();
        $post->title= $data->title;       
        $post->content=$data->content;
        $post->typePost=$data->typePost;
        $post->community_id=$community_id;
        $post->user_id=$user_id;
        $post->save();
    }
    public function GetAllPost(Request $data){
    $usertoken = $data->header('token');
    $user=User::with('Subscribe_Communities')->where('email_verification_token',$usertoken)->first();
    $Subscribe_Communities=$user->Subscribe_Communities;
    $community_id=$Subscribe_Communities->pluck('community_id');    
    $posts= Post::whereIn('location_id', $community_id)->where('location_type','Communitie')->get();
    return response()->json($posts, 200);
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
    public function AddComment(Request $data){
    
    }
}
