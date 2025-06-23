<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Community_Manager;
use App\Models\Communitie;

class CommunityController extends Controller
{
    public function index()
    {

        $managedCommunityIds = Community_Manager::where('user_id', Auth::id())
            ->pluck('community_id');

    
        $communities = Communitie::whereIn('id', $managedCommunityIds)
            ->withCount([
                'subscribers as total_subscribers',
                'subscribers as student_subscribers'
            ])
            ->get();

        return view('admin.communities.index', [
            'communities' => $communities
        ]);
    }
}
