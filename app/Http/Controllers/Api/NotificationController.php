<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Notifications\NewMessageNotification;

class NotificationController extends Controller
{
          public function get_all_notification(Request $data) {
    $user = $data->user();
    $notifications = $user->notifications->map(function ($notification) {
        return [
            'id' => $notification->id,
            'title' => $notification->data['title'],
            'body' => $notification->data['body'],
            'comment' => $notification->data['comment'],
            'is_read' => $notification->read_at !== null, 
            'created_at' => $notification->created_at
        ];
    });
    
    return response()->json($notifications, 200);
}
public function markAsRead(Request $request, $id)
{
    $user = $request->user();
    
    $notification = $user->notifications()->findOrFail($id);
    $notification->markAsRead();
    
    return response()->json(['message' => 'تم تعليم الإشعار كمقروء'], 200);
}
public function markAllAsRead(Request $request)
{
    $user = $request->user();
    $user->unreadNotifications->markAsRead();
    
    return response()->json(['message' => 'تم تعليم جميع الإشعارات كمقروءة'], 200);
}
}
