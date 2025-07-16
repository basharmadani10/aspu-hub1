@extends('layouts.admin')

@section('title', 'Posts in ' . $community->name)

@section('content')
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b-2 border-indigo-500 pb-3">
        Posts in: <span class="text-indigo-700">{{ $community->name }}</span>
    </h1>

    <div class="flex items-center justify-between mb-6">
        <p class="text-gray-700 text-lg">Manage and view posts within the "{{ $community->name }}" community.</p>
        {{-- You might add a button to create a new post here --}}
        {{-- Example: <a href="{{ route('admin.communities.posts.create', $community->id) }}" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span>+</span> Create New Post
        </a> --}}
    </div>

    <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-200">
        @if ($posts->isEmpty())
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-md" role="alert">
                <p class="font-bold">No Posts Found!</p>
                <p>There are no posts in this community yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-100">
                            <th class="px-5 py-3 text-left border-b-2 border-gray-200">Title</th>
                            <th class="px-5 py-3 text-left border-b-2 border-gray-200">Author</th>
                            <th class="px-5 py-3 text-left border-b-2 border-gray-200">Published On</th>
                            <th class="px-5 py-3 text-center border-b-2 border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach ($posts as $post)
                            <tr class="border-b border-gray-200 hover:bg-indigo-50">
                                <td class="px-5 py-5 text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ Str::limit($post->title, 70) }}</p>
                                    @if($post->content)
                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($post->content, 100) }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-5 text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $post->user->first_name ?? 'N/A' }} {{ $post->user->last_name ?? '' }}</p>
                                </td>
                                <td class="px-5 py-5 text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $post->created_at->format('M d, Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-5 text-sm text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- Placeholder action buttons: Replace # with actual routes to view/edit/delete posts --}}
                                        <a href="#" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        <form action="{{ route('admin.communities.posts.destroy', [$community->id, $post->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $posts->links() }} {{-- Pagination links --}}
            </div>
        @endif
    </div>

    <div class="mt-8 flex flex-wrap gap-4 justify-end">
        <a href="{{ route('admin.communities.show', $community->id) }}" class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
            <svg class="-ml-1 mr-3 h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
            Back to Community Details
        </a>
    </div>
@endsection
