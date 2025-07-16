<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Roadmap;
use App\Models\Subject;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoadmapController extends Controller
{
    public function index()
    {
        $roadmaps = Roadmap::with(['specialization' => function($query) {
            $query->select('SpecializationID', 'name');
        }])->paginate(10);

        return view('admin.roadmaps.index', compact('roadmaps'));
    }

    public function create()
    {
        $specializations = Specialization::all();
        $subjects = Subject::orderBy('name')->get();
        return view('admin.roadmaps.create', compact('specializations', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roadmaps,name',
            'description' => 'nullable|string',
            'specialization_id' => 'nullable|exists:specializations,SpecializationID',
            'type' => 'required|string|in:Outside,Inside',
            'subjects' => 'nullable|array',
            'subjects.*.id' => 'required|exists:subjects,id',

        ]);

        $roadmap = Roadmap::create($request->only('name', 'description', 'specialization_id', 'type'));

        if ($request->has('subjects')) {
            $syncData = [];
            foreach ($request->subjects as $subject) {
                $syncData[] = $subject['id'];
            }

            $roadmap->subjects()->sync($syncData);
        }

        return redirect()->route('admin.roadmaps.index')->with('success', 'Roadmap created successfully!');
    }

    public function show(Roadmap $roadmap)
    {
  
        $roadmap->load('subjects.requiredPrerequisites', 'specialization'); 
        return view('admin.roadmaps.show', compact('roadmap'));
    }

    public function edit(Roadmap $roadmap)
    {
        $specializations = Specialization::all();
        $subjects = Subject::orderBy('name')->get();
        $currentSubjects = $roadmap->subjects->pluck('id')->toArray(); 

        return view('admin.roadmaps.edit', compact('roadmap', 'specializations', 'subjects', 'currentSubjects'));
    }

    public function update(Request $request, Roadmap $roadmap)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roadmaps,name,' . $roadmap->id,
            'description' => 'nullable|string',
            'specialization_id' => 'nullable|exists:specializations,SpecializationID',
            'type' => 'required|string|in:Outside,Inside',
            'subjects' => 'nullable|array',
            'subjects.*.id' => 'required|exists:subjects,id',
        ]);

        $roadmap->update($request->only('name', 'description', 'specialization_id', 'type'));

        if ($request->has('subjects')) {
            $syncData = [];
            foreach ($request->subjects as $subject) {
                $syncData[] = $subject['id']; 
            }
            $roadmap->subjects()->sync($syncData);
        } else {
            $roadmap->subjects()->detach();
        }

        return redirect()->route('admin.roadmaps.index')->with('success', 'Roadmap updated successfully!');
    }

    public function destroy(Roadmap $roadmap)
    {
        $roadmap->delete();
        return redirect()->route('admin.roadmaps.index')->with('success', 'Roadmap deleted successfully!');
    }
}
