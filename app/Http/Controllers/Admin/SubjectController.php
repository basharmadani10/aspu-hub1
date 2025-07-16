<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Specialization;
use App\Models\PreviousSubjects;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $specializations = Specialization::with('subjects.requiredPrerequisites')->get();
        return view('admin.subjects.index', ['specializations' => $specializations]);
    }

    public function create()
    {
        $specializations = Specialization::all();
        $subjects = Subject::all();
        return view('admin.subjects.create', compact('specializations', 'subjects'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'             => 'required|string|max:255|unique:subjects,name',
            'hour_count'       => 'required|integer|min:1',
            'Description'      => 'nullable|string',
            'SpecializationID' => 'required|exists:specializations,SpecializationID',
            'prerequisites'    => 'nullable|array',
            'prerequisites.*'  => 'exists:subjects,id',
            'references_text'  => 'nullable|string', 
        ]);

        $references = [];
        if (!empty($validatedData['references_text'])) {
            $lines = explode("\n", $validatedData['references_text']);
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (!empty($trimmedLine)) {
                    $references[] = ['title' => $trimmedLine, 'href' => $trimmedLine]; 
                }
            }
        }
        $validatedData['references'] = $references; 
        unset($validatedData['references_text']); 

        $validatedData['status'] = 'approved';

        $subject = Subject::create($validatedData);

        if (isset($validatedData['prerequisites'])) {
            $subject->requiredPrerequisites()->sync($validatedData['prerequisites']);
        } else {
            $subject->requiredPrerequisites()->detach(); 
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully!');
    }

    public function edit(Subject $subject)
    {
        $specializations = Specialization::all();
        $subjects = Subject::all();

        $currentPrerequisites = $subject->requiredPrerequisites->pluck('id')->toArray();
        $referencesText = '';
        if ($subject->references) {
            foreach ($subject->references as $ref) {
                $referencesText .= ($ref['href'] ?? $ref['title'] ?? '') . "\n";
            }
            $referencesText = trim($referencesText);
        }

        return view('admin.subjects.edit', compact('subject', 'specializations', 'subjects', 'currentPrerequisites', 'referencesText'));
    }


    public function update(Request $request, Subject $subject)
    {
        $validatedData = $request->validate([
            'name'             => [
                'required', 'string', 'max:255',
                Rule::unique('subjects')->ignore($subject->id),
            ],
            'hour_count'       => 'required|integer|min:1',
            'Description'      => 'nullable|string',
            'SpecializationID' => 'required|exists:specializations,SpecializationID',
            'prerequisites'    => 'nullable|array',
            'prerequisites.*'  => 'exists:subjects,id',
            'references_text'  => 'nullable|string', 
        ]);

        $references = [];
        if (!empty($validatedData['references_text'])) {
            $lines = explode("\n", $validatedData['references_text']);
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (!empty($trimmedLine)) {
                    $references[] = ['title' => $trimmedLine, 'href' => $trimmedLine];
                }
            }
        }
        $validatedData['references'] = $references;
        unset($validatedData['references_text']);


        $subject->update($validatedData);
        if (isset($validatedData['prerequisites'])) {
            $subject->requiredPrerequisites()->sync($validatedData['prerequisites']);
        } else {
            $subject->requiredPrerequisites()->detach();
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully!');
    }


    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
