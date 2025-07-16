<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Communitie;
use App\Models\Post;
use Illuminate\Http\Request;

class CommunityPostController extends Controller
{

    public function index(Communitie $community)
    {
        $posts = $community->posts()->with('user')->latest()->paginate(10);
        return view('admin.communities.posts.index', compact('community', 'posts'));
    }


    public function destroy(Communitie $community, Post $post)
    {
        if ($post->community_id !== $community->id) {
            abort(404, 'Post not found in this community.');
        }

        $post->delete();

        return redirect()->back()->with('success', 'Post deleted successfully!');
    }
}
