<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSubject;
use App\Models\UserSemester;
use App\Models\Communitie;
use App\Models\Subscribe_Communities;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserSubjectController extends Controller
{

    public function getAllSubjectsForInitialSetup()
    {

        $subjects = Subject::all(['id', 'name', 'hour_count']);
        return response()->json(['subjects' => $subjects]);
    }


    public function submitInitialCompletedSubjects(Request $request)
    {
        $request->validate(['subjects' => 'present|array']);
        $user = Auth::user();

       
        $semester = UserSemester::firstOrCreate(['userID' => $user->id]);

        foreach ($request->subjects as $subjectID) {
            UserSubject::updateOrCreate(
                ['userID' => $user->id, 'subjectID' => $subjectID],
                [
                    'semesterID' => $semester->id,
                    'has_been_finished' => true
                ]
            );
        }

        return $this->recalculateAndUpdateHours($user);
    }


    public function registerSubjectsForSemester(Request $request)
    {
        $request->validate(['subjects' => 'required|array']);
        $user = Auth::user();
        $semester = UserSemester::where('userID', $user->id)->latest('id')->firstOrFail();

        foreach ($request->subjects as $subjectID) {
            UserSubject::create([
                'userID' => $user->id,
                'subjectID' => $subjectID,
                'semesterID' => $semester->id,
                'has_been_finished' => false
            ]);
        }

        return response()->json(['message' => 'Subjects for the current semester registered successfully.'], 201);
    }


    public function completeSubjects(Request $request)
    {
        $request->validate(['subjects' => 'required|array']);
        $user = Auth::user();

        UserSubject::where('userID', $user->id)
            ->whereIn('subjectID', $request->subjects)
            ->where('has_been_finished', false)
            ->update(['has_been_finished' => true]);

        return $this->recalculateAndUpdateHours($user);
    }


    public function changeSpecialization(Request $request)
    {
        $request->validate(['specialization' => 'required|string|exists:specializations,name']);
        $user = Auth::user();


        if ($user->number_of_completed_hours < 96) {
            return response()->json(['message' => 'Insufficient hours to select a specialization.'], 403);
        }

        $newSpecializationName = $request->specialization;
        $newCommunity = Communitie::where('name', $newSpecializationName)->firstOrFail();
        $currentSubscription = Subscribe_Communities::where('user_id', $user->id)->first();

        if ($currentSubscription && $currentSubscription->community_id == $newCommunity->id) {
            return response()->json(['message' => 'User is already in the correct community.']);
        }

        DB::transaction(function () use ($user, $currentSubscription, $newCommunity) {
            if ($currentSubscription) {
                $oldCommunity = Communitie::find($currentSubscription->community_id);
                $currentSubscription->delete();
                if ($oldCommunity) $oldCommunity->decrement('subscriber_count');
            }

            Subscribe_Communities::create(['user_id' => $user->id, 'community_id' => $newCommunity->id]);
            $newCommunity->increment('subscriber_count');
        });

        return response()->json(['message' => 'Specialization and community subscription updated successfully.']);
    }


    private function recalculateAndUpdateHours(User $user)
    {
        $completedSubjects = UserSubject::where('userID', $user->id)
            ->where('has_been_finished', true)
            ->with('subject:id,hour_count')
            ->get();

        $totalHours = $completedSubjects->sum('subject.hour_count');
        $user->update(['number_of_completed_hours' => $totalHours]);

        $isSpecializationTime = ($totalHours >= 96);

        return response()->json([
            'success' => true,
            'is_specialization_time' => $isSpecializationTime,
            'total_completed_hours' => $totalHours,
        ]);
    }
}
