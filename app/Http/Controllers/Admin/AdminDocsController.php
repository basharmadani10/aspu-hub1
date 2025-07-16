<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Docs;
use App\Models\DocsType;
use App\Models\Subject;

class AdminDocsController extends Controller
{
    /**
     * Display a listing of documents.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {

        $docs = Docs::with('docsType', 'subject')
                    ->latest()
                    ->paginate(10);

        return view('admin.docs.index', compact('docs'));
    }


    public function create()
    {
        $docsTypes = DocsType::whereIn('name', ['lecture', 'summary'])->get();


        $subjects = Subject::with('specialization')->get();

        return view('admin.docs.create', compact('docsTypes', 'subjects'));
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'doc_name'        => ['required', 'string', 'max:255'],
            'docs_type_id'    => ['required', 'exists:docs_types,id'],
            'subject_id'      => ['required', 'exists:subjects,id'],
            'document_file'   => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max:10240'],
        ]);

        $file = $request->file('document_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('public/lectures', $fileName);

        Docs::create([
            'doc_name'     => $validatedData['doc_name'],
            'docs_type_id' => $validatedData['docs_type_id'],
            'subject_id'   => $validatedData['subject_id'],
            'doc_url'      => Storage::url($filePath),
        ]);

        return redirect()->route('admin.docs.index')->with('success', 'Document uploaded successfully!');
    }


    public function destroy(Docs $doc)
    {
        $filePath = str_replace('/storage/', 'public/', $doc->doc_url);
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        $doc->delete();

        return redirect()->route('admin.docs.index')->with('success', 'Document deleted successfully!');
    }
}
