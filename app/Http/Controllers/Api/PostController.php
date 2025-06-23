<?php

namespace App\Http\Controllers\Api;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use App\Models\User;
use App\Models\Photo;
use App\Models\Comment;
use App\Models\PostVote;
use App\Models\Tag;
use App\Notifications\NewMessageNotification;

class PostController extends Controller
{
    /**
     * Create a new post.
     */
    public function Addpost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'typePost' => 'required|string',
            'community_id' => 'required|exists:communities,id',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $submittedTagNames = json_decode($validatedData['tags']);

        if (!is_array($submittedTagNames)) {
            return response()->json(['errors' => ['tags' => 'Tags input must be a valid JSON array of tag names.']], 422);
        }

        $submittedTagNames = array_filter(array_map('trim', $submittedTagNames), fn($tagName) => !empty($tagName));
        $allowedTagNames = Tag::pluck('name')->all();
        $validTagsToStore = [];
        $invalidSubmittedTags = [];

        if (!empty($submittedTagNames)) {
            foreach ($submittedTagNames as $tagName) {
                if (in_array($tagName, $allowedTagNames)) {
                    $validTagsToStore[] = $tagName;
                } else {
                    $invalidSubmittedTags[] = $tagName;
                }
            }

            if (!empty($invalidSubmittedTags)) {
                return response()->json([
                    'message' => 'One or more submitted tags are not allowed. Please use existing tags.',
                    'invalid_tags' => $invalidSubmittedTags,
                    'allowed_tags_suggestion' => $allowedTagNames
                ], 422);
            }
        }

        try {

            $post = DB::transaction(function () use ($request, $validatedData, $validTagsToStore) {
                $newPost = Post::create([
                    'title' => $validatedData['title'],
                    'content' => $validatedData['content'],
                    'typePost' => $validatedData['typePost'],
                    'community_id' => $validatedData['community_id'],
                    'user_id' => auth()->id(),
                    'positiveVotes' => 0,
                    'negativeVotes' => 0,
                    'tags' => json_encode(array_unique($validTagsToStore)),
                ]);

                if ($request->hasFile('images')) {
                    $filesToProcess = $request->file('images');
                    if (!is_array($filesToProcess)) {
                        $filesToProcess = $filesToProcess ? [$filesToProcess] : [];
                    }

                    foreach ($filesToProcess as $image) {
                        if ($image->isValid()) {
                            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $path = $image->storeAs('posts/' . $newPost->id, $imageName, 'public');
                            Photo::create([
                                'post_id' => $newPost->id,
                                'photo' => $path,
                            ]);
                        }
                    }
                }
                return $newPost; // Return the post from the transaction
            });

            return response()->json([
                'message' => 'Post created successfully with validated tags.',
                'post' => $post->load('photos')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred while creating the post.'], 500);
        }
    }


    public function updatePost(Request $request, Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to edit this post.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'typePost' => 'sometimes|string',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags' => 'sometimes|json',
            'images_to_delete' => 'sometimes|array',
            'images_to_delete.*' => 'sometimes|integer|exists:photos,id,post_id,' . $post->id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {

            if ($request->has('images_to_delete')) {
                $photosToDelete = Photo::where('post_id', $post->id)
                    ->whereIn('id', $request->input('images_to_delete', []))
                    ->get();
                foreach ($photosToDelete as $photo) {
                    Storage::disk('public')->delete($photo->photo);
                    $photo->delete();
                }
            }


            if ($request->hasFile('images')) {
                $filesToProcess = $request->file('images');
                if (!is_array($filesToProcess)) {
                    $filesToProcess = [$filesToProcess];
                }
                foreach ($filesToProcess as $image) {
                    if ($image->isValid()) {
                        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = $image->storeAs('posts/' . $post->id, $imageName, 'public');
                        Photo::create(['post_id' => $post->id, 'photo' => $path]);
                    }
                }
            }

            // Section 3: Update text and tags
            if ($request->has('tags')) {
                // (Your existing tag update logic would go here)
                $post->tags = $request->input('tags');
            }

            $post->fill($request->only(['title', 'content', 'typePost']));
            $post->save();
            DB::commit();

            return response()->json([
                'message' => 'Post updated successfully.',
                'post' => $post->refresh()->load('photos')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating post ID ' . $post->id . ': ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred while updating the post.'], 500);
        }
    }

    /**
     * Delete a post.
     */
    public function deletePost(Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this post.'], 403);
        }


        DB::transaction(function () use ($post) {

            foreach ($post->photos as $photo) {
                Storage::disk('public')->delete($photo->photo);
            }


            $post->delete();
        });

        return response()->json(['message' => 'Post deleted successfully.'], 200);
    }


    public function votePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'vote' => 'required|string|in:up,down'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $postId = $request->input('post_id');
        $voteAction = $request->input('vote');
        $userId = auth()->id();
        $post = Post::findOrFail($postId);
        $message = '';

        try {
            DB::transaction(function () use ($userId, $postId, $voteAction, $post, &$message) {
                $existingVote = PostVote::where('user_id', $userId)
                    ->where('post_id', $postId)
                    ->first();

                if ($existingVote) {
                    if ($existingVote->vote_type === $voteAction) {
                        // User is clicking the same button again, so remove the vote
                        $existingVote->delete();
                        $voteAction === 'up' ? $post->decrement('positiveVotes') : $post->decrement('negativeVotes');
                        $message = 'Vote removed.';
                    } else {
                        // User is changing their vote
                        $oldVoteType = $existingVote->vote_type;
                        $existingVote->vote_type = $voteAction;
                        $existingVote->save();

                        // Adjust counts
                        $oldVoteType === 'up' ? $post->decrement('positiveVotes') : $post->decrement('negativeVotes');
                        $voteAction === 'up' ? $post->increment('positiveVotes') : $post->increment('negativeVotes');
                        $message = 'Vote changed successfully.';
                    }
                } else {
                    // New vote
                    PostVote::create([
                        'user_id' => $userId,
                        'post_id' => $postId,
                        'vote_type' => $voteAction,
                    ]);
                    $voteAction === 'up' ? $post->increment('positiveVotes') : $post->increment('negativeVotes');
                    $message = 'Vote cast successfully.';
                }

                // Ensure counts don't go below zero
                $post->positiveVotes = max(0, $post->positiveVotes);
                $post->negativeVotes = max(0, $post->negativeVotes);
                $post->save();
            });

            return response()->json([
                'message' => $message,
                'votes' => [
                    'positive' => $post->positiveVotes,
                    'negative' => $post->negativeVotes,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Vote processing error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred while processing your vote.'], 500);
        }
    }



    public function report(Request $request, Post $post)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $post->reports()->create([
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'reportable_id' => $post->id,
            'reportable_type' => get_class($post),
        ]);

        return response()->json(['message' => 'Your report has been submitted successfully.'], 201);

}
}
