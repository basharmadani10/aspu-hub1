{{-- File: resources/views/admin/docs/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Upload New Lecture/Document')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Upload New Lecture/Document</h2>
        <a href="{{ route('admin.docs.index') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200">
            &larr; Back to Documents
        </a>
    </div>

    <div class="p-8 bg-white rounded-lg shadow-md">
        <form action="{{ route('admin.docs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Document Name -->
                <div>
                    <label for="doc_name" class="block mb-2 text-sm font-medium text-gray-700">Document Name</label>
                    <input type="text" name="doc_name" id="doc_name" value="{{ old('doc_name') }}" required
                           class="w-full px-4 py-2 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('doc_name') border-red-500 @enderror">
                    @error('doc_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Type -->
                <div>
                    <label for="docs_type_id" class="block mb-2 text-sm font-medium text-gray-700">Document Type</label>
                    <select name="docs_type_id" id="docs_type_id" required
                            class="w-full px-4 py-2 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('docs_type_id') border-red-500 @enderror">
                        <option value="">Select a document type</option>
                        {{-- ستعرض هذه الحلقة فقط الأنواع التي تم جلبها من الكونترولر (محاضرة، ملخص) --}}
                        @foreach($docsTypes as $type)
                            <option value="{{ $type->id }}" {{ old('docs_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('docs_type_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subject for Document -->
                <div class="md:col-span-2">
                    <label for="subject_id" class="block mb-2 text-sm font-medium text-gray-700">Associate with Subject</label>
                    <select name="subject_id" id="subject_id" required
                            class="w-full px-4 py-2 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('subject_id') border-red-500 @enderror">
                        <option value="">Select a subject</option>
                        @forelse($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }} ({{ $subject->specialization->name ?? 'N/A' }}) {{-- NEW: Display Specialization --}}
                            </option>
                        @empty
                            <option value="" disabled>No subjects available. Please add some.</option>
                        @endforelse
                    </select>
                    @error('subject_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Upload -->
                <div class="md:col-span-2">
                    <label for="document_file" class="block mb-2 text-sm font-medium text-gray-700">Upload Document (PDF, DOCX, PPTX, XLSX)</label>
                    <input type="file" name="document_file" id="document_file" required
                           class="w-full px-4 py-2 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('document_file') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Max file size: 10MB</p>
                    @error('document_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-3 font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-200">
                    Upload Document
                </button>
            </div>
        </form>
    </div>
@endsection
