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

        $query = Notification::where(function ($q) use ($user) {
            // Personal notifications
            $q->where('user_id', $user->id)
                // Or global notifications
                ->orWhereNull('user_id');
        });

        // Filter by plant for non-admin users
        if ($user->role !== 'admin') {
            $query->where(function ($q) use ($user) {
                // Only show notifications for user's plant
                // Check if data->plant_id exists and matches user's plant
                $q->whereRaw("JSON_EXTRACT(data, '$.plant_id') = ?", [$user->plant_id])
                    // Or notifications without plant_id (old notifications or global)
                    ->orWhereRaw("JSON_EXTRACT(data, '$.plant_id') IS NULL");
            });
        }

        $notifications = $query->unread()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $unreadCountQuery = Notification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereNull('user_id');
        });

        // Apply same plant filter for unread count
        if ($user->role !== 'admin') {
            $unreadCountQuery->where(function ($q) use ($user) {
                $q->whereRaw("JSON_EXTRACT(data, '$.plant_id') = ?", [$user->plant_id])
                    ->orWhereRaw("JSON_EXTRACT(data, '$.plant_id') IS NULL");
            });
        }

        $unreadCount = $unreadCountQuery->unread()->count();

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

    /**
     * Clear all notifications (delete permanently)
     */
    public function clearAll()
    {
        $user = auth()->user();

        // Delete all notifications visible to this user (personal + global)
        Notification::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereNull('user_id');
        })->delete();

        return response()->json(['success' => true, 'message' => 'Semua notifikasi berhasil dihapus']);
    }
}
