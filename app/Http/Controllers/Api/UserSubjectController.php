<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\User;
use App\Models\Docs;
use App\Models\UserSubject;
use App\Models\UserSemester;
use App\Models\Communitie;
use App\Models\Subscribe_Communities;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;
use Carbon\Carbon; 

class UserSubjectController extends Controller
{

    public function getAllSubjectsForInitialSetup()
    {
        $subjects = Subject::all(['id', 'name', 'hour_count']);
        return response()->json(['subjects' => $subjects]);
    }


    public function submitInitialCompletedSubjects(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subjects' => 'nullable|array', 
            'subjects.*' => 'required|integer|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();


        if ($user->initial_subjects_configured) {
            return response()->json(['message' => 'Initial completed subjects have already been configured for this user.'], 403);
        }

        try {
            DB::beginTransaction();


            $initialSemester = UserSemester::where('userID', $user->id)
                                        ->orderBy('id', 'asc') 
                                        ->first();

            if (!$initialSemester) {
                throw new \Exception('No initial semester record found for user. Please contact support.');
            }


            if (!empty($request->subjects)) {
                foreach ($request->subjects as $subjectID) {
                    UserSubject::updateOrCreate(
                        [
                            'userID' => $user->id,
                            'subjectID' => $subjectID,
                            'semesterID' => $initialSemester->id, 
                        ],
                        [
                            'has_been_finished' => true, 
                            'has_been_canceled' => false,
                            'mark' => 100, 
                        ]
                    );
                }
            }


            $user->initial_subjects_configured = true;
            $user->save();

            DB::commit();

            return $this->recalculateAndUpdateHours($user);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in submitInitialCompletedSubjects: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred during submission of initial subjects.'], 500);
        }
    }


    public function registerSubjectsForSemester(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required|integer|exists:subjects,id', // Ensure each subject ID exists
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $currentSemester = UserSemester::where('userID', $user->id)
                                         ->orderBy('id', 'desc')
                                         ->first();

        if (!$currentSemester) {
            return response()->json(['message' => 'No active semester found for your account. Please contact support.'], 404);
        }


        if ($currentSemester->has_registered_subjects) {
            return response()->json(['message' => 'You have already registered subjects for the current semester. You cannot register again until the next semester begins.'], 403);
        }


        $today = Carbon::now()->toDateString(); 
        if ($today < $currentSemester->start_date || $today > $currentSemester->end_date) {
            return response()->json(['message' => 'Subject registration for the current semester is currently closed. Please check the registration period.'], 403);
        }

        DB::beginTransaction();
        try {
            foreach ($request->subjects as $subjectID) {
                // Prevent enrolling in a subject they've already successfully completed (from past or current semesters)
                $hasFinishedBefore = UserSubject::where('userID', $user->id)
                                                ->where('subjectID', $subjectID)
                                                ->where('has_been_finished', true)
                                                ->exists();

                if ($hasFinishedBefore) {
                    DB::rollBack();
                    return response()->json(['message' => "You have already successfully completed subject ID {$subjectID}. You cannot register for it again."], 422);
                }

                // Prevent enrolling in the same subject twice in the same semester
                $alreadyEnrolledThisSemester = UserSubject::where('userID', $user->id)
                                                ->where('semesterID', $currentSemester->id)
                                                ->where('subjectID', $subjectID)
                                                ->exists();
                if ($alreadyEnrolledThisSemester) {
                    DB::rollBack();
                    return response()->json(['message' => "You are already enrolled in subject ID {$subjectID} for this semester."], 422);
                }

                UserSubject::create([
                    'userID' => $user->id,
                    'subjectID' => $subjectID,
                    'semesterID' => $currentSemester->id,
                    'has_been_finished' => false, // Subjects registered for the current semester are not yet finished
                    'has_been_canceled' => false,
                    'mark' => 0, // Default mark until grades are processed
                ]);
            }

            // **NEW: Mark this semester as having registered subjects after successful registration**
            $currentSemester->has_registered_subjects = true;
            $currentSemester->save();

            DB::commit();

            return response()->json(['message' => 'Subjects for the current semester registered successfully.'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error registering subjects for semester: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred while registering subjects.'], 500);
        }
    }

    /**
     * Mark subjects as completed (e.g., after semester ends and grades are in).
     */
    public function completeSubjects(Request $request)
    {
        $request->validate([
            'subjects' => 'required|array',
            'subjects.*' => 'required|integer|exists:subjects,id',
        ]);
        $user = Auth::user();

        // This method updates any unfinished subject for the user.
        // If you need to complete subjects specifically for one semester, pass a `semesterID`.
        UserSubject::where('userID', $user->id)
            ->whereIn('subjectID', $request->subjects)
            ->where('has_been_finished', false)
            ->update(['has_been_finished' => true]);

        return $this->recalculateAndUpdateHours($user);
    }

    /**
     * Change user's specialization based on completed hours.
     */
    public function changeSpecialization(Request $request)
    {
        $request->validate(['specialization' => 'required|string|exists:specializations,name']);
        $user = Auth::user();

        // Example threshold for specialization selection
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


    public function getCompletedSubjects()
    {
        $user = Auth::user();

        $completedSubjects = UserSubject::where('userID', $user->id)
            ->where('has_been_finished', true)
            ->with('subject:id,name,hour_count')
            ->get()
            ->pluck('subject'); // Get only the subject details

        return response()->json(['completed_subjects' => $completedSubjects]);
    }


    private function recalculateAndUpdateHours(User $user)
    {
        $completedSubjects = UserSubject::where('userID', $user->id)
            ->where('has_been_finished', true)
            ->with('subject:id,hour_count') // Eager load only necessary subject fields
            ->get();

        $totalHours = $completedSubjects->sum('subject.hour_count');
        $user->update(['number_of_completed_hours' => $totalHours]);

        $isSpecializationTime = ($totalHours >= 96); // Use >= in case total hours exceed 96

        return response()->json([
            'success' => true,
            'is_specialization_time' => $isSpecializationTime,
            'total_completed_hours' => $totalHours,
            'message' => 'User hours updated successfully.'
        ]);
    }

    /**
     * Get subjects that the user has not yet enrolled in (neither completed nor currently taking).
     * This shows subjects they could potentially take in the future.
     */
    public function getUnfinishedSubjects()
    {
        $user = Auth::user();


        $enrolledSubjectIDs = UserSubject::where('userID', $user->id)
            ->pluck('subjectID')
            ->toArray();

        $unfinishedSubjects = Subject::whereNotIn('id', $enrolledSubjectIDs)
            ->select('id', 'name', 'hour_count')
            ->get();

        return response()->json(['subjects' => $unfinishedSubjects]);
    }


    public function getCurrentSemesterSubjects(Request $request)
    {
        $user = Auth::user();

        $currentSemester = UserSemester::where('userID', $user->id)
            ->orderBy('id', 'desc') // Get the latest semester
            ->first();

        if (!$currentSemester) {
            return response()->json(['subjects' => [], 'message' => 'No current semester subjects found.'], 200);
        }

        $subjects = UserSubject::where('userID', $user->id)
            ->where('semesterID', $currentSemester->id)
            ->where('has_been_finished', false) // Only get subjects not yet finished in this semester
            ->where('has_been_canceled', false) // Exclude canceled subjects
            ->with('subject:id,name,hour_count')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($userSubject) {
                return [
                    'id' => $userSubject->subject->id,
                    'name' => $userSubject->subject->name,
                    'hour_count' => $userSubject->subject->hour_count,
                    'registered_at' => $userSubject->created_at ? $userSubject->created_at->format('Y-m-d H:i:s') : null
                ];
            });

        return response()->json([
            'semester_id' => $currentSemester->id,
            'semester_number' => $currentSemester->semester_number,
            'start_date' => $currentSemester->start_date,
            'end_date' => $currentSemester->end_date,
            'has_registered_subjects' => $currentSemester->has_registered_subjects,
            'subjects' => $subjects
        ]);
    }


    public function checkRegistrationStatus()
    {
        $user = Auth::user();

        $currentSemester = UserSemester::where('userID', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        $isRegistrationAllowed = false;
        $message = '';

        if (!$currentSemester) {
            $isRegistrationAllowed = true;
            $message = 'No semester record found. You are ready to register subjects for your first semester on the platform.';
        } elseif (!$user->initial_subjects_configured) {
            $isRegistrationAllowed = false;
            $message = 'Please submit your previously completed subjects first to configure your academic record.';
        } elseif ($currentSemester->has_registered_subjects) {
            $isRegistrationAllowed = false;
            $message = 'You have already registered subjects for the current semester. You cannot register again until the next semester begins.';
        } else {
            $today = Carbon::now()->toDateString();
            if ($today >= $currentSemester->start_date && $today <= $currentSemester->end_date) {
                $isRegistrationAllowed = true;
                $message = 'You are ready to register subjects for the current semester.';
            } else {
                $isRegistrationAllowed = false;
                $message = 'Subject registration for the current semester is currently outside the allowed period (' . $currentSemester->start_date . ' to ' . $currentSemester->end_date . ').';
            }
        }

        return response()->json([
            'is_registration_allowed' => $isRegistrationAllowed,
            'message' => $message,
        ]);
    }

    public function getSubjectDocuments($subjectId)
    {
        try {
            $subject = Subject::findOrFail($subjectId);

            $documents = Docs::where('subject_id', $subjectId)
                ->with('docsType:id,name,description')
                ->get()
                ->map(function ($doc) {
                    $pathForStorageUrl = ltrim($doc->doc_url, '/');
                    $pathForStorageUrl = preg_replace('/^storage\//', '', $pathForStorageUrl);
                    $pathForStorageUrl = str_replace('//', '/', $pathForStorageUrl);

                    return [
                        'id' => $doc->DocID,
                        'title' => $this->generateReadableTitle($doc->doc_url),
                        'url' => url(Storage::url($pathForStorageUrl)),
                        'type' => [
                            'name' => $doc->docsType->name ?? 'Unknown',
                            'description' => $doc->docsType->description ?? '',
                        ],
                        'uploaded_at' => $doc->created_at ? $doc->created_at->format('Y-m-d H:i:s') : null,
                    ];
                });

            return response()->json([
                'subject_name' => $subject->name,
                'documents' => $documents,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching subject documents: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjectLectures($subjectId)
    {
        try {
            $subject = Subject::findOrFail($subjectId);

            $lectures = Docs::where('subject_id', $subjectId)
                ->whereHas('docsType', function ($query) {
                    $query->where('name', 'Lecture');
                })
                ->with('docsType:id,name,description')
                ->get()
                ->map(function ($doc) {
                    $pathForStorageUrl = ltrim($doc->doc_url, '/');
                    $pathForStorageUrl = preg_replace('/^storage\//', '', $pathForStorageUrl);
                    $pathForStorageUrl = str_replace('//', '/', $pathForStorageUrl);

                    return [
                        'id' => $doc->DocID,
                        'title' => $this->generateReadableTitle($doc->doc_url),
                        'url' => url(Storage::url($pathForStorageUrl)),
                        'type' => [
                            'name' => $doc->docsType->name ?? 'Unknown',
                            'description' => $doc->docsType->description ?? '',
                        ],
                        'uploaded_at' => $doc->created_at ? $doc->created_at->format('Y-m-d H:i:s') : null,
                    ];
                });

            return response()->json([
                'subject_name' => $subject->name,
                'lectures' => $lectures,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching subject lectures: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjectSummaries($subjectId)
    {
        try {
            $subject = Subject::findOrFail($subjectId);

            $summaries = Docs::where('subject_id', $subjectId)
                ->whereHas('docsType', function ($query) {
                    $query->where('name', 'Summary');
                })
                ->with('docsType:id,name,description')
                ->get()
                ->map(function ($doc) {
                    $pathForStorageUrl = ltrim($doc->doc_url, '/');
                    $pathForStorageUrl = preg_replace('/^storage\//', '', $pathForStorageUrl);
                    $pathForStorageUrl = str_replace('//', '/', $pathForStorageUrl);

                    return [
                        'id' => $doc->DocID,
                        'title' => $this->generateReadableTitle($doc->doc_url),
                        'url' => url(Storage::url($pathForStorageUrl)),
                        'type' => [
                            'name' => $doc->docsType->name ?? 'Unknown',
                            'description' => $doc->docsType->description ?? '',
                        ],
                        'uploaded_at' => $doc->created_at ? $doc->created_at->format('Y-m-d H:i:s') : null,
                    ];
                });

            return response()->json([
                'subject_name' => $subject->name,
                'summaries' => $summaries,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching subject summaries: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateReadableTitle($filePath)
    {
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        return ucwords(str_replace(['_', '-'], ' ', $fileName));
    }
}
