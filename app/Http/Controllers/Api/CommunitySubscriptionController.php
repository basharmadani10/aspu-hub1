<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Communitie;
use App\Models\Subscribe_Communities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommunitySubscriptionController extends Controller
{

    public function subscribe(Request $request)
    {
        $request->validate([
            'community_id' => 'required|exists:communities,id',
        ]);

        $communityId = $request->input('community_id');
        $userId = Auth::id();


        $existingSubscription = Subscribe_Communities::where('user_id', $userId)
            ->where('community_id', $communityId)
            ->first();

        if ($existingSubscription) {
            return response()->json(['message' => 'You are already subscribed to this community.'], 409); // 409 Conflict
        }


        Subscribe_Communities::create([
            'user_id' => $userId,
            'community_id' => $communityId,
        ]);


        Communitie::find($communityId)->increment('subscriber_count');

        return response()->json(['message' => 'Successfully subscribed.'], 201);
    }
}
