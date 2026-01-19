<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get recent notifications for the dropdown
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $notifications = Notification::where(function ($query) use ($user) {
            // Personal notifications
            $query->where('user_id', $user->id)
                // Or global notifications for their plant
                ->orWhereNull('user_id');
        })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $unreadCount = Notification::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereNull('user_id');
        })
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        if ($notification) {
            $notification->update(['is_read' => true]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        Notification::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereNull('user_id');
        })
            ->unread()
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
