<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationRoadmapController extends Controller
{
    public function getSpecializationRoadmaps()
    {
        $specializations = Specialization::with([
            'roadmaps' => function ($query) {
                $query->with([
                    'subjects' => function ($subQuery) {
                   
                        $subQuery->select('subjects.id', 'subjects.name', 'subjects.hour_count as hours', 'subjects.references') 
                                 ->with('requiredPrerequisites:id,name');
                    }
                ]);
            }
        ])->get();

        $formattedSpecializations = $specializations->map(function ($specialization) {
            $roadmapsData = $specialization->roadmaps->map(function ($roadmap) {
                $subjectsData = $roadmap->subjects->map(function ($subject) {
                    $prerequisitesNames = [];
                    if ($subject->relationLoaded('requiredPrerequisites')) {
                        $prerequisitesNames = $subject->requiredPrerequisites->pluck('name')->toArray();
                    }
                    $subjectReferences = $subject->references ?? []; 


                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'hours' => $subject->hours,
                        'prerequisites' => $prerequisitesNames,
                        'references' => $subjectReferences, 
                    ];
                });

                return [
                    'id' => $roadmap->id,
                    'name' => $roadmap->name,
                    'type' => $roadmap->type,
                    'description' => $roadmap->description,
                    'subjects' => $subjectsData,
                ];
            });

            return [
                'id' => $specialization->SpecializationID,
                'name' => $specialization->name,
                'description' => $specialization->description,
                'is_for_university' => $specialization->is_for_university,
                'roadmaps' => $roadmapsData,
            ];
        });

        return response()->json($formattedSpecializations);
    }
}
