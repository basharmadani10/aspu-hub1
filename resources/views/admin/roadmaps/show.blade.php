@extends('layouts.admin')

@section('title', 'Roadmap Details: ' . $roadmap->name)

@section('content')
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b-2 border-indigo-500 pb-3">
        Roadmap Details: <span class="text-indigo-700">{{ $roadmap->name }}</span>
    </h1>

    {{-- Roadmap General Information Card --}}
    <div class="bg-white shadow-xl rounded-xl p-8 mb-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-3">General Information</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Name:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $roadmap->name }}</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Specialization:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    {{ $roadmap->specialization->name ?? 'None' }}
                </p>
            </div>
            {{-- ADDED: Display Type --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Type:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $roadmap->type }}</p>
            </div>
            {{-- END ADDED Field --}}
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Description:</h3>
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200 min-h-[80px]">
                <p class="text-gray-800 text-base leading-relaxed">{{ $roadmap->description ?? 'No description provided.' }}</p>
            </div>
        </div>
    </div>

    {{-- Subjects in Roadmap Card --}}
    <div class="bg-white shadow-xl rounded-xl p-8 mb-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-3">Subjects in this Roadmap</h2>
        @if ($roadmap->subjects->isEmpty())
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-md" role="alert">
                <p class="font-bold">No Subjects Assigned!</p>
                <p>This roadmap doesn't have any subjects yet. Click "Edit Roadmap" to add some.</p>
            </div>
        @else
            <ol class="space-y-4">
                {{-- No longer sorting by pivot.order here since we're not using it directly --}}
                @foreach ($roadmap->subjects as $subject)
                    <li class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 flex items-start space-x-3">
                        {{-- REMOVED: Order display --}}
                        {{-- <span class="flex-shrink-0 text-indigo-600 font-bold text-xl">{{ $subject->pivot->order + 1 }}.</span> --}}
                        <div class="flex-grow">
                            <h4 class="text-lg font-semibold text-gray-900">{{ $subject->name }}
                                <span class="text-sm font-normal text-gray-600">({{ $subject->hour_count }} hours)</span>
                            </h4>
                            @if ($subject->Description)
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ Str::limit($subject->Description, 120) }}
                                </p>
                            @endif
                            @if ($subject->requiredPrerequisites->isNotEmpty())
                                <p class="text-sm text-gray-600 mt-1">
                                    **Prerequisites:** {{ $subject->requiredPrerequisites->pluck('name')->join(', ') }}
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div class="mt-8 flex flex-wrap gap-4 justify-end">
        <a href="{{ route('admin.roadmaps.edit', $roadmap) }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150 ease-in-out">
            <svg class="-ml-1 mr-3 h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zm-5.636 5.636l-1.414 1.414L10.586 16H16v-5.414l-7.05-7.05-1.414 1.414z"></path></svg>
            Edit Roadmap
        </a>
        <form action="{{ route('admin.roadmaps.destroy', $roadmap) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this roadmap? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                <svg class="-ml-1 mr-3 h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                Delete Roadmap
            </button>
        </form>
        <a href="{{ route('admin.roadmaps.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus::ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
            <svg class="-ml-1 mr-3 h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
            Back to Roadmaps
        </a>
    </div>
@endsection
