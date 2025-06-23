<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentVote;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewMessageNotification; // Assuming this notification class exists

class CommentController extends Controller
{

    public function addComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'           => 'required|string',
            'post_id'           => 'required|exists:posts,id',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $comment = Comment::create([
            'content'           => $validatedData['content'],
            'post_id'           => $validatedData['post_id'],
            'user_id'           => auth()->id(),
            'parent_comment_id' => $validatedData['parent_comment_id'] ?? null,
            'positive_votes'    => 0,
            'negative_votes'    => 0,
        ]);

        $comment->load('post.user', 'user');


        if ($comment->post && $comment->post->user && $comment->user && $comment->post->user->id !== $comment->user->id) {
            $postOwner = $comment->post->user;
            try {
                $postOwner->notify(new NewMessageNotification($comment));
            } catch (\Exception $e) {
                Log::error("Failed to send notification for comment ID {$comment->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment
        ], 201);
    }


    public function voteComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|exists:comments,id',
            'vote'       => 'required|string|in:up,down'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $commentId     = $validatedData['comment_id'];
        $voteAction    = $validatedData['vote'];
        $userId        = auth()->id();

        $comment = Comment::findOrFail($commentId);

        $existingVote = CommentVote::where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->first();
        $message = '';

        DB::beginTransaction();
        try {
            if ($existingVote) {
                if ($existingVote->vote_type === $voteAction) {
                    $existingVote->delete();
                    if ($voteAction === 'up') {
                        $comment->decrement('positive_votes');
                    } else {
                        $comment->decrement('negative_votes');
                    }
                    $message = 'Comment vote removed successfully.';
                } else {
                    $oldVoteType = $existingVote->vote_type;
                    $existingVote->vote_type = $voteAction;
                    $existingVote->save();

                    if ($oldVoteType === 'up') {
                        $comment->decrement('positive_votes');
                    } else {
                        $comment->decrement('negative_votes');
                    }

                    if ($voteAction === 'up') {
                        $comment->increment('positive_votes');
                    } else {
                        $comment->increment('negative_votes');
                    }
                    $message = 'Comment vote changed successfully.';
                }
            } else {
                CommentVote::create([
                    'user_id'    => $userId,
                    'comment_id' => $commentId,
                    'vote_type'  => $voteAction,
                ]);

                if ($voteAction === 'up') {
                    $comment->increment('positive_votes');
                } else {
                    $comment->increment('negative_votes');
                }
                $message = 'Comment vote Created  successfully.';
            }

            $comment->positive_votes = max(0, $comment->positive_votes);
            $comment->negative_votes = max(0, $comment->negative_votes);
            $comment->save();

            DB::commit();

            return response()->json([
                'message' => $message,
                'votes'   => [
                    'positive' => $comment->positive_votes,
                    'negative' => $comment->negative_votes,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comment vote processing error: ' . $e->getMessage() . ' for comment_id: ' . $commentId . ' user_id: ' . $userId, ['exception' => $e]);
            return response()->json(['message' => 'An error occurred while processing your vote on the comment.', 'dev_error' => $e->getMessage()], 500);
        }
    }


    public function updateComment(Request $request, Comment $comment)
    {

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to edit this comment.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment->content = $request->input('content');
        $comment->save();

        return response()->json([
            'message' => 'Comment updated successfully.',
            'comment' => $comment->load('user')
        ], 200);
    }


    public function deleteComment(Request $request, Comment $comment)
    {

        if ($request->user()->id !== $comment->user_id) {

            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        }


        $comment->delete();


        return response()->json(['message' => 'Comment deleted successfully'], 200);
    }
    public function report(Request $request, Comment $comment)
    {

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $comment->reports()->create([
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'reportable_id' => $comment->id,
            'reportable_type' => get_class($comment),
        ]);

        return response()->json(['message' => 'Your report has been submitted successfully.'], 201);

}
}
