@extends('layouts.admin')

@section('title', 'Edit Roadmap: ' . $roadmap->name)

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Roadmap: {{ $roadmap->name }}</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.roadmaps.update', $roadmap) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Roadmap Name:</label>
                <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name', $roadmap->name) }}" required>
                @error('name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror">{{ old('description', $roadmap->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="specialization_id" class="block text-gray-700 text-sm font-bold mb-2">Associate with Specialization (Optional):</label>
                <select name="specialization_id" id="specialization_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('specialization_id') border-red-500 @enderror">
                    <option value="">-- Select Specialization --</option>
                    @foreach ($specializations as $specialization)
                        <option value="{{ $specialization->SpecializationID }}" {{ old('specialization_id', $roadmap->specialization_id) == $specialization->SpecializationID ? 'selected' : '' }}>
                            {{ $specialization->name }}
                        </option>
                    @endforeach
                </select>
                @error('specialization_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            {{-- Type Selection Field --}}
            <div class="mb-6">
                <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Roadmap Type:</label>
                <select name="type" id="type" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('type') border-red-500 @enderror" required>
                    <option value="">-- Select Type --</option>
                    <option value="Outside" {{ old('type', $roadmap->type) == 'Outside' ? 'selected' : '' }}>Outside</option>
                    <option value="Inside" {{ old('type', $roadmap->type) == 'Inside' ? 'selected' : '' }}>Inside</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <h2 class="text-xl font-bold text-gray-800 mb-4">Subjects in Roadmap:</h2>
            <div id="subjects-container" class="space-y-4 mb-6">
                {{-- Subject inputs will be added here by JavaScript --}}
            </div>
            <button type="button" id="add-subject-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">Add Subject</button>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Roadmap
                </button>
                <a href="{{ route('admin.roadmaps.index') }}" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subjectsContainer = document.getElementById('subjects-container');
            const addSubjectBtn = document.getElementById('add-subject-btn');
            let subjectIndex = 0;

            const allSubjects = @json($subjects->pluck('name', 'id'));
            const currentSubjects = @json($roadmap->subjects->pluck('id')); // <--- تم التعديل هنا: جلب IDs فقط

            function addSubjectField(subjectId = '') { // REMOVED: order parameter
                const newSubjectDiv = document.createElement('div');
                newSubjectDiv.classList.add('flex', 'items-center', 'space-x-4');
                newSubjectDiv.innerHTML = `
                    <div class="flex-1">
                        <label for="subject-${subjectIndex}" class="block text-gray-700 text-sm font-bold mb-2">Subject:</label>
                        <select name="subjects[${subjectIndex}][id]" id="subject-${subjectIndex}" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">-- Select Subject --</option>
                            ${Object.entries(allSubjects).map(([id, name]) => `<option value="${id}" ${id == subjectId ? 'selected' : ''}>${name}</option>`).join('')}
                        </select>
                    </div>
                    {{-- REMOVED: Order input field --}}
                    {{-- <div class="w-24">
                        <label for="order-${subjectIndex}" class="block text-gray-700 text-sm font-bold mb-2">Order:</label>
                        <input type="number" name="subjects[${subjectIndex}][order]" id="order-${subjectIndex}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="${order}" min="0" required>
                    </div> --}}
                    <button type="button" class="remove-subject-btn bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded self-end mb-2">Remove</button>
                `;
                subjectsContainer.appendChild(newSubjectDiv);

                newSubjectDiv.querySelector('.remove-subject-btn').addEventListener('click', function() {
                    newSubjectDiv.remove();
                });
                subjectIndex++;
            }

            addSubjectBtn.addEventListener('click', function() {
                addSubjectField();
            });

            // Populate existing subjects when editing
            @if (!old('subjects') && count($currentSubjects) > 0) // Only run if no old inputs and there are current subjects
                // No need to clear subjectsContainer.innerHTML here if it's empty by default or handled by initial logic
                subjectIndex = 0; // Reset index for repopulation
                @foreach ($currentSubjects as $subjectId)
                    addSubjectField("{{ $subjectId }}"); // Pass subject ID only
                @endforeach
            @endif

            // If there are old inputs due to validation error, re-populate them
            @if (old('subjects'))
                // Clear initial populated subjects if there are old inputs
                subjectsContainer.innerHTML = '';
                subjectIndex = 0; // Reset index for repopulation
                @foreach (old('subjects') as $index => $subject)
                    addSubjectField("{{ $subject['id'] }}"); // REMOVED: order parameter
                @endforeach
            @endif
        });
    </script>
@endsection
