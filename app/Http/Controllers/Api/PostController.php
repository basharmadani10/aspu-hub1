<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Photo;
use App\Models\Video;

class PostController extends Controller
{
    public function AddNewPost(Request $data){
    
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
    
    }
    public function AddComment(Request $data){
    
    }
}
