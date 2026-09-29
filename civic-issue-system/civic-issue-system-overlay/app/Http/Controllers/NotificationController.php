<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** AJAX: polled by the navbar bell. */
    public function unread(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'items' => $user->unreadNotifications()->latest()->take(8)->get()->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'title' => $n->data['title'] ?? '',
                'time' => $n->created_at->diffForHumans(),
                'url' => route('notifications.open', $n->id),
            ]),
        ]);
    }

    public function open(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($request->user()->complaintUrl($notification->data['complaint_id']));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
