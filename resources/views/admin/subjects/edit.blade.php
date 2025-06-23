@extends('layouts.admin')

@section('title', 'Edit Subject')

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Edit Subject: {{ $subject->name }}</h2>
        <a href="{{ route('admin.subjects.index') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition duration-150 ease-in-out">
            &larr; Back to Subjects List
        </a>
    </div>

    <div class="p-8 bg-white rounded-lg shadow-xl">
        <form action="{{ route('admin.subjects.update', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2"> {{-- Increased gap for better spacing --}}
                {{-- Subject Name --}}
                <div>
                    <label for="name" class="block mb-2 text-sm font-semibold text-gray-700">Subject Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $subject->name) }}" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Hour Count --}}
                <div>
                    <label for="hour_count" class="block mb-2 text-sm font-semibold text-gray-700">Hour Count</label>
                    <input type="number" name="hour_count" id="hour_count" value="{{ old('hour_count', $subject->hour_count) }}" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    @error('hour_count')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Specialization --}}
                <div class="md:col-span-2">
                    <label for="SpecializationID" class="block mb-2 text-sm font-semibold text-gray-700">Specialization</label>
                    <select name="SpecializationID" id="SpecializationID" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                        <option value="">Select a specialization</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec->SpecializationID }}" {{ old('SpecializationID', $subject->SpecializationID) == $spec->SpecializationID ? 'selected' : '' }}>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('SpecializationID')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Prerequisites --}}
                <div class="md:col-span-2">
                    <label for="prerequisites" class="block mb-2 text-sm font-semibold text-gray-700">Prerequisites (Optional)</label>
                    <select name="prerequisites[]" id="prerequisites" multiple class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent h-48 overflow-y-auto">
                        @foreach($subjects as $sub)
                            {{-- Prevent a subject from being its own prerequisite --}}
                            @if ($sub->id !== $subject->id)
                                <option value="{{ $sub->id }}" {{ in_array($sub->id, old('prerequisites', $currentPrerequisites)) ? 'selected' : '' }}>
                                    {{ $sub->name }} ({{ $sub->specialization->name ?? 'N/A' }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong class="text-indigo-700">How to select/deselect:</strong>
                        Hold <kbd class="px-1 py-0.5 border rounded bg-gray-100 text-gray-700 text-xs">Ctrl</kbd> (Windows/Linux) or
                        <kbd class="px-1 py-0.5 border rounded bg-gray-100 text-gray-700 text-xs">Cmd</kbd> (Mac) and click to select multiple subjects or to deselect an already selected subject.
                    </p>
                    @error('prerequisites')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @error('prerequisites.*')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="Description" class="block mb-2 text-sm font-semibold text-gray-700">Description</label>
                    <textarea name="Description" id="Description" rows="4" class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">{{ old('Description', $subject->Description) }}</textarea>
                    @error('Description')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="mt-8 text-right"> {{-- Align button to the right --}}
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-4 0V4a1 1 0 011-1h2a1 1 0 011 1v3m-4 0h4"></path></svg>
                    Update Subject
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
