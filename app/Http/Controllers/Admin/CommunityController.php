<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
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

    public function create()
    {

        return view('admin.communities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

        ]);


        $community = Communitie::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);


        Community_Manager::create([
            'user_id' => Auth::id(),
            'community_id' => $community->id,
        ]);

        return redirect()->route('admin.communities.index')
            ->with('success', 'Community created successfully.');
    }

    public function edit($id)
    {
        $community = Communitie::findOrFail($id);

        return view('admin.communities.edit', compact('community'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $community = Communitie::findOrFail($id);

        $community->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.communities.index')
            ->with('success', 'Community updated successfully.');
    }

    public function destroy($id)
    {
        $community = Communitie::findOrFail($id);

        $community->managers()->delete();
        $community->subscribers()->delete();
        $community->posts()->delete();

        $community->delete();

        return redirect()->route('admin.communities.index')
            ->with('success', 'Community deleted successfully.');
    }

    public function show($id)
    {
        $community = Communitie::withCount('subscribers')->findOrFail($id);

        return view('admin.communities.show', compact('community'));
    }
}
