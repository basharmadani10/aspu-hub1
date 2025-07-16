<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Photo;
use App\Models\Video;
use App\Models\Comment;
use DB;
use Illuminate\Support\Facades\Hash;
use App\Notifications\NewMessageNotification;

class CummunityController extends Controller
{
    public function GetAllPost(Request $request)
    {
        $user = $request->user();
        $community_ids = $user->Subscribe_Communities()->pluck('community_id');
        $postsFromSubscribedCommunities = Post::whereIn('community_id', $community_ids)
            ->where('user_id', '!=', $user->id)
            ->with(['user:id,first_name,last_name,image', 'comments.user:id,first_name', 'photos']) 
            ->withCount([
                'postVotes as positiveVotes' => function($query) {
                    $query->where('vote_type', 'up');
                },
                'postVotes as negativeVotes' => function($query) {
                    $query->where('vote_type', 'down');
                }
            ])
            ->orderByDesc('created_at')
            ->get();
        $userOwnPosts = Post::where('user_id', $user->id)
            ->with(['user:id,first_name,last_name,image', 'comments.user:id,first_name', 'photos']) 
            ->withCount([
                'postVotes as positiveVotes' => function($query) {
                    $query->where('vote_type', 'up');
                },
                'postVotes as negativeVotes' => function($query) {
                    $query->where('vote_type', 'down');
                }
            ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'posts_from_subscribed_communities' => $postsFromSubscribedCommunities,
            'user_own_posts' => $userOwnPosts,
        ], 200);
    }

    public function allPostGeneral()
    {
        return $this->getCommunityPosts(1);
    }

    public function allPostSoftware()
    {
        return $this->getCommunityPosts(2);
    }

    public function allPostNetwork()
    {
        return $this->getCommunityPosts(3);
    }

    protected function getCommunityPosts($communityId)
    {
        $posts = Post::where('community_id', $communityId)
            ->with(['user:id,first_name,last_name,image', 'comments.user:id,first_name', 'photos', 'postVotes']) // <--- Changed profile_image to image here
            ->withCount([
                'postVotes as upvotes' => function($query) {
                    $query->where('vote_type', 'up');
                },
                'postVotes as downvotes' => function($query) {
                    $query->where('vote_type', 'down');
                }
            ])
            ->orderByDesc('upvotes')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'posts' => $posts
        ], 200);
    }
}
