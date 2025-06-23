<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Specialization;
use App\Models\PreviousSubjects; // إضافة نموذج PreviousSubjects
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * Display a listing of the subjects.
     * عرض قائمة بالمواد.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $specializations = Specialization::with('subjects.requiredPrerequisites')->get(); // تحميل المتطلبات المسبقة
        return view('admin.subjects.index', ['specializations' => $specializations]);
    }

    /**
     * Show the form for creating a new subject.
     * عرض النموذج لإنشاء مادة جديدة.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $specializations = Specialization::all();
        $subjects = Subject::all(); // جلب جميع المواد لاختيار المتطلبات المسبقة
        return view('admin.subjects.create', compact('specializations', 'subjects'));
    }

    /**
     * Store a newly created subject in storage.
     * تخزين مادة جديدة في قاعدة البيانات.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'             => 'required|string|max:255|unique:subjects,name', // للتأكد من أن الاسم فريد
            'hour_count'       => 'required|integer|min:1',
            'Description'      => 'nullable|string',
            'SpecializationID' => 'required|exists:specializations,SpecializationID',
            'prerequisites'    => 'nullable|array', // المتطلبات المسبقة (يمكن أن تكون مصفوفة)
            'prerequisites.*'  => 'exists:subjects,id', // كل عنصر في المصفوفة يجب أن يكون ID لمادة موجودة
        ]);

        $validatedData['status'] = 'approved'; // تعيين الحالة إلى معتمدة افتراضياً

        $subject = Subject::create($validatedData);

        // حفظ المتطلبات المسبقة إذا تم تحديدها
        if (isset($validatedData['prerequisites'])) {
            foreach ($validatedData['prerequisites'] as $prerequisiteId) {
                // التأكد من أن المادة لا تكون متطلباً مسبقاً لنفسها
                if ($prerequisiteId != $subject->id) {
                    PreviousSubjects::create([
                        'subjectID'         => $subject->id,
                        'PreviousSubjectID' => $prerequisiteId,
                    ]);
                }
            }
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully!');
    }

    /**
     * Show the form for editing the specified subject.
     * عرض النموذج لتعديل المادة المحددة.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\View\View
     */
    public function edit(Subject $subject)
    {
        $specializations = Specialization::all();
        $subjects = Subject::all(); // جلب جميع المواد لاختيار المتطلبات المسبقة
        // جلب المتطلبات المسبقة الحالية لهذه المادة
        $currentPrerequisites = $subject->requiredPrerequisites->pluck('id')->toArray();

        return view('admin.subjects.edit', compact('subject', 'specializations', 'subjects', 'currentPrerequisites'));
    }

    /**
     * Update the specified subject in storage.
     * تحديث المادة المحددة في قاعدة البيانات.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
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
            'prerequisites'    => 'nullable|array', // المتطلبات المسبقة
            'prerequisites.*'  => 'exists:subjects,id', // كل عنصر يجب أن يكون ID لمادة موجودة
        ]);

        $subject->update($validatedData);

        // تحديث المتطلبات المسبقة: حذف الموجودة وإضافة الجديدة
        PreviousSubjects::where('subjectID', $subject->id)->delete(); // حذف جميع المتطلبات الحالية
        if (isset($validatedData['prerequisites'])) {
            foreach ($validatedData['prerequisites'] as $prerequisiteId) {
                // التأكد من أن المادة لا تكون متطلباً مسبقاً لنفسها
                if ($prerequisiteId != $subject->id) {
                    PreviousSubjects::create([
                        'subjectID'         => $subject->id,
                        'PreviousSubjectID' => $prerequisiteId,
                    ]);
                }
            }
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully!');
    }

    /**
     * Remove the specified subject from storage.
     * حذف المادة المحددة من قاعدة البيانات.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}

