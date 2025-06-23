@extends('layouts.admin')

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">My Supervised Communities</h2>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md">
        <div class="p-4">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-semibold text-gray-500 uppercase border-b">
                        <th class="p-3">Community Name</th>
                        <th class="p-3">Total Subscribers</th>
                        <th class="p-3">Student Subscribers</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($communities as $community)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800">{{ $community->name }}</td>
                            <td class="p-3 text-gray-600">{{ $community->total_subscribers }}</td>
                            <td class="p-3 text-gray-600">{{ $community->student_subscribers }}</td>
                            <td class="p-3 text-sm text-center">
                                <a href="#" class="px-3 py-1 font-medium text-indigo-600 bg-indigo-100 rounded-md hover:bg-indigo-200">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">You are not supervising any communities yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
