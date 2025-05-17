<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSubject;
use App\Models\UserSemester;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserSubjectController extends Controller
{
    public function getSelectableSubjects(Request $request)
    {
        $user = Auth::user();
        $currentSemester = UserSemester::where('userID', $user->id)
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$currentSemester) {
            return response()->json(['subjects' => []]);
        }

        $specializationID = $currentSemester->SpecializationID;

        $subjects = Subject::where('SpecializationID', $specializationID)
            ->orWhere('SpecializationID', 1)
            ->get(['id', 'name', 'hour_count']);

        return response()->json([
            'subjects' => $subjects
        ]);
    }

    public function storeUserSubjects(Request $request)
    {
        $user = Auth::user();
        $subjectIDs = $request->subjects;

        $semester = UserSemester::where('userID', $user->id)
            ->latest('id')
            ->firstOrFail();

        foreach ($subjectIDs as $subjectID) {
            $subject = Subject::find($subjectID);
            if (!$subject) {
                continue;
            }

            // تأكد من عدم تكرار المادة لنفس الطالب
            $userSubject = UserSubject::where('userID', $user->id)
                ->where('subjectID', $subjectID)
                ->first();

            if (!$userSubject) {
                UserSubject::create([
                    'userID' => $user->id,
                    'subjectID' => $subjectID,
                    'semesterID' => $semester->id,
                    'has_been_finished' => true,
                    'has_been_canceled' => false,
                ]);
            } else {
          
                $userSubject->has_been_finished = true;
                $userSubject->save();
            }
        }


        $completedSubjects = UserSubject::where('userID', $user->id)
            ->where('has_been_finished', true)
            ->with('subject')
            ->get();

        $totalHours = $completedSubjects->sum(function ($item) {
            return $item->subject->hour_count;
        });

        $user->number_of_completed_hours = $totalHours;
        $user->save();

        return response()->json([
            'success' => true,
            'total_completed_hours' => $totalHours,
        ]);
    }




public function getAvailableSubjectsToSelect(Request $request)
{
    $user = Auth::user();

    $currentSemester = UserSemester::where('userID', $user->id)
        ->orderBy('start_date', 'desc')
        ->first();

    if (!$currentSemester) {
        return response()->json(['subjects' => []]);
    }

    $specializationID = $currentSemester->SpecializationID;


    $allSubjects = Subject::where('SpecializationID', $specializationID)
        ->orWhere('SpecializationID', 1)
        ->get();


    $finishedSubjectIDs = UserSubject::where('userID', $user->id)
        ->where('has_been_finished', true)
        ->pluck('subjectID')
        ->toArray();


    $availableSubjects = $allSubjects->filter(function ($subject) use ($finishedSubjectIDs) {
        return !in_array($subject->id, $finishedSubjectIDs);
    })->values();

    return response()->json([
        'subjects' => $availableSubjects
    ]);
}


public function confirmSelectedSubjectsThisSemester(Request $request)
{
    $user = Auth::user();
    $subjectIDs = $request->subjects;


    $currentSemester = UserSemester::where('userID', $user->id)
        ->orderBy('start_date', 'desc')
        ->firstOrFail();

    $totalHours = 0;

    foreach ($subjectIDs as $subjectID) {
        $subject = Subject::find($subjectID);
        if (!$subject) {
            continue;
        }

        // إحضار المادة إن وجدت في جدول user_subjects
        $userSubject = UserSubject::where('userID', $user->id)
            ->where('subjectID', $subjectID)
            ->first();

        if ($userSubject) {
            // إذا كانت المادة موجودة مسبقًا ولكن لم تُنهى
            if (!$userSubject->has_been_finished) {
                $userSubject->has_been_finished = true;
                $userSubject->semesterID = $currentSemester->id; // تحديث الفصل
                $userSubject->save();

                $totalHours += $subject->hour_count;
            }
        } else {
            // إذا لم تكن موجودة مسبقًا، يتم إضافتها وإنهاؤها مباشرة
            UserSubject::create([
                'userID' => $user->id,
                'subjectID' => $subjectID,
                'semesterID' => $currentSemester->id,
                'has_been_finished' => true,
                'has_been_canceled' => false,
            ]);

            $totalHours += $subject->hour_count;
        }
    }


    $currentSemester->semester_hours = $totalHours;
    $currentSemester->save();

    return response()->json([
        'success' => true,
        'semester_hours' => $totalHours,
        'message' => 'Subjects confirmed and semester hours updated.'
    ]);
}




}
