<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Specialization;
use App\Models\Subject; 

class SpecializationController extends Controller
{

    public function index()
    {
        $specializations = Specialization::all();
        return view('admin.specializations.index', compact('specializations'));
    }


    public function create()
    {
        return view('admin.specializations.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name',
            'description' => 'required|string',
            'is_for_university' => 'required|boolean',
        ]);

        Specialization::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_for_university' => $request->is_for_university,
        ]);

        return redirect()->route('admin.specializations.index')
                         ->with('success', 'Specialization created successfully.');
    }


    public function show($id)
    {
        $specialization = Specialization::findOrFail($id);
        return view('admin.specializations.show', compact('specialization'));
    }


    public function edit($id)
    {
        $specialization = Specialization::findOrFail($id);
        return view('admin.specializations.edit', compact('specialization'));
    }


    public function update(Request $request, $id)
    {
        $specialization = Specialization::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name,' . $specialization->SpecializationID . ',SpecializationID',
            'description' => 'required|string',
            'is_for_university' => 'required|boolean',
        ]);

        $specialization->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_for_university' => $request->is_for_university,
        ]);

        return redirect()->route('admin.specializations.index')
                         ->with('success', 'Specialization updated successfully.');
    }


    public function destroy($id)
    {
        $specialization = Specialization::findOrFail($id);


        $specialization->subjects()->delete();


        $specialization->delete();

        return redirect()->route('admin.specializations.index')
                         ->with('success', 'Specialization and its associated subjects deleted successfully.');
    }
}
