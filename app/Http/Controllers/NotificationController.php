<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function redirectNotification($username, $id)
    {
        $this->middleware('editOwn');

        $notification = Notification::findOrFail($id);

        $notification->seen = 1;
        $notification->save();
        if ($notification->link != null) {
            return redirect(url($notification->link));
        }

        return redirect(url('/'));
    }

    public function markAllRead()
    {
        $notifications = Notification::where('user_id', Auth::user()->id)->with('notified_from')->update(['seen' => 1]);
    }
}
