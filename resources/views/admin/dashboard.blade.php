{{--
    File: resources/views/admin/dashboard.blade.php
    Description: This file has been refactored to extend the main admin layout.
    All the duplicate HTML structure and sidebar have been removed, and it now
    correctly uses the @section('content') directive.
--}}
@extends('layouts.admin')

@section('title', 'Supervisor Dashboard')

@section('content')

    <h2 class="text-3xl font-bold text-gray-800">Welcome, {{ Auth::user()->first_name }}!</h2>
    <p class="mb-8 text-gray-600">Here's a summary of your activities.</p>

    <!-- Stats Cards with Dynamic Data -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Post Reports</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $postReportsCount }}</p>
                </div>
                <div class="p-3 text-red-600 bg-red-100 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Comment Reports</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $commentReportsCount }}</p>
                </div>
                 <div class="p-3 text-yellow-600 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $pendingRequestsCount }}</p>
                </div>
                <div class="p-3 text-blue-600 bg-blue-100 rounded-full">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Supervised Groups Card -->
        <a href="{{ route('admin.communities.index') }}" class="block p-6 transition bg-white rounded-lg shadow-md hover:bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Supervised Groups</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $supervisedGroupsCount }}</p>
                </div>
                <div class="p-3 text-green-600 bg-green-100 rounded-full">
                   <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Reports Table -->
    <div class="mt-10 bg-white rounded-lg shadow-md">
        <div class="p-4 border-b">
            <h3 class="text-xl font-semibold text-gray-800">Recent Reports</h3>
        </div>
        <div class="p-4 overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-semibold text-gray-500 uppercase border-b">
                        <th class="p-3">Reported Content</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Reporter</th>
                        <th class="p-3">Date</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReports as $report)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 text-gray-700">
                                @if ($report->reportable)
                                    "{{ Str::limit($report->reportable->title ?? $report->reportable->content, 50) }}"
                                @else
                                    <span class="text-red-500 italic">[Content Deleted]</span>
                                @endif
                            </td>
                            <td class="p-3 text-gray-700 capitalize">{{ Str::afterLast($report->reportable_type, '\\') }}</td>
                            <td class="p-3 text-gray-700">{{ $report->reporter->first_name ?? 'N/A' }}</td>
                            <td class="p-3 text-gray-500">{{ $report->created_at?->format('Y-m-d') }}</td>
                            <td class="p-3 text-sm text-center">
                                <a href="{{ route('admin.reports.show', $report->id) }}" class="px-3 py-1 font-medium text-indigo-600 bg-indigo-100 rounded-md hover:bg-indigo-200">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">No pending reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
