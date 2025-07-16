<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Roadmap;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    public function getRoadmaps()
    {
        $roadmaps = Roadmap::with([
            'specialization:SpecializationID,name',
            'subjects' => function ($query) {
                $query->select('subjects.id', 'subjects.name', 'subjects.hour_count as hours')
                      ->withPivot('order')
                      ->orderBy('roadmap_subjects.order')
                      ->with('requiredPrerequisites:id,name'); 
            }
        ])->get();


        $roadmaps->each(function ($roadmap) {
            $roadmap->subjects->each(function ($subject) {

                if ($subject->relationLoaded('requiredPrerequisites')) {
                    $subject->prerequisites = $subject->requiredPrerequisites->pluck('name')->toArray();
                } else {
                    $subject->prerequisites = [];
                }
            });
        });

        return response()->json($roadmaps);
    }
}
