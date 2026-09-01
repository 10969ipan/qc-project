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
        // Release session lock immediately for read-only AJAX endpoint to prevent request blocking / 11s pending
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $user = auth()->user();
        $sevenDaysAgo = now()->subDays(7);

        // Utilize composite index (user_id, is_read, type) first
        $query = Notification::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->unread()
            ->where('created_at', '>=', $sevenDaysAgo);

        // Filter by plant for non-admin users
        if ($user->role !== 'admin') {
            $query->where(function ($q) use ($user) {
                $q->whereRaw("JSON_EXTRACT(data, '$.plant_id') = ?", [$user->plant_id])
                    ->orWhereRaw("JSON_EXTRACT(data, '$.plant_id') IS NULL");
            });
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Dynamically correct and absolute-ize URLs relative to the current request host & subdirectory
        $notifications->transform(function ($notification) {
            $data = $notification->data;
            if (isset($data['checksheet_id']) && isset($data['checksheet_type'])) {
                $params = ['id' => $data['checksheet_id']];
                $type = $data['checksheet_type'];
                
                switch ($type) {
                    case 'In Process':
                        $data['url'] = route('in_process.index', $params);
                        break;
                    case 'Cross Cut':
                        $data['url'] = route('cross_cut.index', $params);
                        break;
                    case 'Cross Cut Painting':
                        $data['url'] = route('cross_cut_painting.index', $params);
                        break;
                    case 'Sortir':
                        $data['url'] = route('sortir.index', $params);
                        break;
                    case 'Plating':
                        $data['url'] = route('plating.index', $params);
                        break;
                    case 'Painting':
                        $data['url'] = route('painting.index', $params);
                        break;
                    case 'Double Tape':
                        $data['url'] = route('double_tape.index', $params);
                        break;
                    case 'First Piece Approval':
                        $data['url'] = route('first_piece_approval.index', $params);
                        break;
                    case 'Incoming Part':
                        $data['url'] = route('incoming.parts.index', $params);
                        break;
                    case 'Incoming Material':
                        $data['url'] = route('incoming.materials.index', $params);
                        break;
                    case 'Incoming Sub-Part':
                        $data['url'] = route('incoming.sub_parts.index', $params);
                        break;
                    case 'Incoming Export':
                        $data['url'] = route('incoming.exports.index', $params);
                        break;
                    case 'Incoming Chemical':
                        $data['url'] = route('incoming.chemicals.index', $params);
                        break;
                    case 'Sub Assy':
                    default:
                        $data['url'] = route('admin.checksheets.index', $params);
                        break;
                }
                
                $notification->data = $data;
            } elseif (isset($data['url']) && is_string($data['url'])) {
                $parsedUrl = parse_url($data['url']);
                $pathAndQuery = ($parsedUrl['path'] ?? '') . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
                
                if (preg_match('/(\/(report|checksheet|calibration|notifications|dashboard|kakotora|admin|verifications)\b.*)/i', $pathAndQuery, $matches)) {
                    $data['url'] = url($matches[1]);
                    $notification->data = $data;
                } else {
                    $basePath = request()->getBaseUrl(); // e.g. "/qc" or "/qc-project/public"
                    $cleanPath = $pathAndQuery;
                    if (!empty($basePath)) {
                        if (strpos($cleanPath, $basePath . '/') === 0) {
                            $cleanPath = substr($cleanPath, strlen($basePath));
                        } elseif ($cleanPath === $basePath) {
                            $cleanPath = '/';
                        }
                    }
                    $data['url'] = url($cleanPath);
                    $notification->data = $data;
                }
            }
            return $notification;
        });

        $unreadCountQuery = Notification::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->unread()
            ->where('created_at', '>=', $sevenDaysAgo);

        // Apply same plant filter for unread count
        if ($user->role !== 'admin') {
            $unreadCountQuery->where(function ($q) use ($user) {
                $q->whereRaw("JSON_EXTRACT(data, '$.plant_id') = ?", [$user->plant_id])
                    ->orWhereRaw("JSON_EXTRACT(data, '$.plant_id') IS NULL");
            });
        }

        $unreadCount = $unreadCountQuery->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark a notification as read.
     * Deletes all notifications for the current user related to the same checksheet,
     * so sibling notifications (same checksheet) are also cleared for this user only.
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['success' => false], 404);
        }

        $user = auth()->user();
        $data = $notification->data ?? [];

        // Delete all notifications for THIS user related to the same checksheet
        if (!empty($data['checksheet_id']) && !empty($data['checksheet_type'])) {
            Notification::where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhereNull('user_id');
                })
                ->whereRaw("JSON_EXTRACT(data, '$.checksheet_id') = ?", [$data['checksheet_id']])
                ->whereRaw("JSON_EXTRACT(data, '$.checksheet_type') = ?", [$data['checksheet_type']])
                ->delete();
        } else {
            // Fallback: just delete this single notification
            $notification->delete();
        }

        return response()->json(['success' => true]);
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
