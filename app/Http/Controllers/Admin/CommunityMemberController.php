<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Communitie;
use App\Models\User;
use Illuminate\Http\Request;

class CommunityMemberController extends Controller
{
    public function index($communityId)
    {
        $community = Communitie::with('subscribers.user')->findOrFail($communityId);


        return view('admin.communities.members.index', compact('community'));
    }


    public function showUserInfo(User $user)
    {
        return view('admin.communities.members.showuser', compact('user'));
    }


    public function toggleBlock(User $user)
    {
        $user->is_blocked = !$user->is_blocked; 
        $user->save();

        $status = $user->is_blocked ? 'blocked' : 'unblocked';

        return redirect()->back()->with('success', "User '{$user->first_name} {$user->last_name}' has been {$status} successfully.");
    }
}
