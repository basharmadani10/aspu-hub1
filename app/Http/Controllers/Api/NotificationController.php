<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Notifications\NewMessageNotification;

class NotificationController extends Controller
{
        public function get_all_notification(Request $data)  {
        $user=$data->user();
        $notifications=$user->notifications->map(function ($notification) {
            return [
                'title' => $notification->data['title'],
                'body' => $notification->data['body'],
                'comment'=>$notification->data['comment']
            ];
        });
        return response()->json($notifications, 200);
        }
}
