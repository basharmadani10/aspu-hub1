@extends('layouts.admin')

@section('title', 'Subject Management')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Subject Management</h2>
    <a href="{{ route('admin.subjects.create') }}" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span>+</span> Add New Subject
    </a>
</div>

{{-- Success Message --}}
@if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="space-y-8">
    {{-- Use forelse to handle the case of no specializations --}}
    @forelse($specializations as $specialization)
        <div class="overflow-hidden bg-white rounded-lg shadow-md">
            <div class="p-5 bg-gray-50 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-700">{{ $specialization->name }}</h3>
            </div>

            {{-- Check if there are any subjects within the specialization --}}
            @if($specialization->subjects->isNotEmpty())
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-sm font-semibold text-gray-500 uppercase bg-gray-100">
                            <th class="p-4">Subject Name</th>
                            <th class="p-4">Hour Count</th>
                            <th class="p-4">Prerequisites</th> {{-- عمود جديد للمتطلبات المسبقة --}}
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach($specialization->subjects as $subject)
                            <tr class="border-b border-gray-200 hover:bg-indigo-50">
                                <td class="p-4 font-medium">{{ $subject->name }}</td>
                                <td class="p-4">{{ $subject->hour_count }}</td>
                                <td class="p-4">
                                    @forelse($subject->requiredPrerequisites as $prerequisite)
                                        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $prerequisite->name }}</span>
                                    @empty
                                        <span class="text-gray-500">None</span>
                                    @endforelse
                                </td>
                                <td class="p-4 text-center space-x-2">
                                    <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Edit</a>
                                    <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                {{-- Message for when a specialization has no subjects --}}
                <div class="p-4 text-center text-gray-500">
                    No subjects have been added to this specialization yet.
                </div>
            @endif
        </div>
    @empty
        {{-- Message for when there are no specializations at all --}}
        <div class="p-10 text-center bg-white rounded-lg shadow-md">
            <h3 class="text-xl font-bold text-gray-700">No Specializations Found</h3>
            <p class="mt-2 text-gray-500">Please add specializations first before assigning subjects to them.</p>
        </div>
    @endforelse
</div>
@endsection

