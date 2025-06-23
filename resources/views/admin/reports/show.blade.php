@extends('layouts.admin')

@section('title', 'Report Details')

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Report Details</h2>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Content: Report Details -->
        <div class="p-6 bg-white rounded-lg shadow-md lg:col-span-2">
            <h3 class="pb-4 mb-4 text-xl font-semibold text-gray-800 border-b">
                Report on a <span class="text-indigo-600">{{ Str::afterLast($report->reportable_type, '\\') }}</span>
            </h3>

            <div class="space-y-4">
                <div>
                    <h4 class="font-semibold text-gray-700">Content:</h4>
                    <blockquote class="p-4 mt-2 text-gray-600 bg-gray-50 border-l-4 border-gray-300">
                        {{-- ✅ هذا هو الكود المحدث الذي يمنع الخطأ --}}
                        @if ($report->reportable)
                            "{{ $report->reportable->title ?? $report->reportable->content }}"
                        @else
                            <span class="italic text-red-500">[Content has been deleted]</span>
                        @endif
                    </blockquote>
                </div>
                 <div>
                    <h4 class="font-semibold text-gray-700">Reason for report:</h4>
                    <p class="mt-1 text-gray-600">{{ $report->reason ?? 'No reason provided.' }}</p>
                </div>
            </div>
        </div>

        <!-- Sidebar: Reporter Info & Actions -->
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="pb-4 mb-4 border-b">
                <h4 class="font-semibold text-gray-700">Reporter Info</h4>
                <p class="text-gray-600">{{ $report->reporter->first_name }} {{ $report->reporter->last_name }}</p>
                <p class="text-sm text-gray-500">{{ $report->reporter->email }}</p>
            </div>

            <div>
                <h4 class="mb-3 font-semibold text-gray-700">Actions</h4>
                <div class="space-y-3">

                    {{-- ✅ إظهار الأزرار فقط إذا كان المحتوى موجودًا --}}
                    @if ($report->reportable)
                        <!-- زر حذف المحتوى -->
                        <form action="{{ route('admin.reports.deleteContent', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this content?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">Delete Content</button>
                        </form>

                        <!-- زر حظر المستخدم -->
                        <form action="{{ route('admin.reports.banUser', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to ban this user? This will also delete the content.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 font-semibold text-white bg-yellow-500 rounded-lg hover:bg-yellow-600">Ban User</button>
                        </form>
                    @else
                        <p class="p-3 text-sm text-center text-gray-500 bg-gray-100 rounded-lg">Actions are disabled because the original content was deleted.</p>
                    @endif

                    <!-- زر تعليم كـ "تم الحل" -->
                    <form action="{{ route('admin.reports.resolve', $report->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Mark as Resolved</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</main>
@endsection
