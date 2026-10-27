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
        $role = $user->role ?? '';
        $approverRoles = ['supervisor', 'supervisor_qc', 'asst_manager', 'asst_manager_qc', 'admin'];
        $notifications = collect();
        $unreadCount = 0;

        // 1. Approval Master Data Item untuk Approver
        if (in_array($role, $approverRoles)) {
            $pendingQuery = \App\Models\Item::withoutGlobalScope('plant')
                ->with(['plant', 'category', 'requester'])
                ->where('approval_status', 'pending');

            $totalPlantId = \App\Models\Plant::where('code', 'total')->value('id');
            $isCrossPlantApprover = in_array($role, ['admin', 'asst_manager', 'asst_manager_qc']);

            if (!$isCrossPlantApprover && !empty($user->plant_id) && $user->plant_id !== $totalPlantId) {
                $pendingQuery->where('plant_id', $user->plant_id);
            }

            if (in_array($role, ['supervisor', 'supervisor_qc'])) {
                $pendingQuery->whereNull('spv_approved_at');
            } elseif (in_array($role, ['asst_manager', 'asst_manager_qc'])) {
                // Wajib SPV approve dulu baru muncul di notifikasi antrean Asst Manager
                $pendingQuery->whereNotNull('spv_approved_at')
                    ->whereNull('asst_mgr_approved_at');
            }

            $unreadCount = (clone $pendingQuery)->count();
            $pendingItems = $pendingQuery->latest('updated_at')->limit(20)->get();

            $notifications = $pendingItems->map(function ($item) {
                $plantName = optional($item->plant)->name ?? 'Plant';
                $plantCode = optional($item->plant)->code ?: 'total';
                $isNew = empty($item->pending_changes);
                $title = ($isNew ? 'Master Item Baru: ' : 'Pembaruan Item: ') . $item->name;
                $message = "Menunggu approval di Plant {$plantName} (Diajukan oleh: " . (optional($item->requester)->name ?? 'Staff') . ")";

                return (object)[
                    'id' => $item->id,
                    'type' => 'master_item_approval',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => false,
                    'created_at' => $item->updated_at ?? $item->created_at,
                    'data' => [
                        'url' => route('admin.items.index', [
                            'plant' => $plantCode,
                            'approval_status' => 'pending',
                            'f_item_id' => $item->id,
                            'item_id' => $item->id
                        ]),
                        'item_id' => $item->id,
                        'is_pending_approval' => true
                    ]
                ];
            });
        }

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
        $user = auth()->user();

        // 1. Cek apakah ini ID dari Master Data Item (Approval Pending)
        $item = \App\Models\Item::withoutGlobalScope('plant')->find($id);
        if ($item) {
            // Notifikasi master item approval tidak dimark-as-read lewat klik;
            // Notifikasi ini hanya akan hilang jika item sudah resmi disetujui (approved) atau ditolak (rejected)
            return response()->json(['success' => true]);
        }

        // 2. Notifikasi reguler dari tabel notifications
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['success' => false], 404);
        }

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

        // Catatan: Item master data yang statusnya pending approval tidak dimark-read
        // Notifikasi approval bersifat persisten dan hanya hilang jika item telah di-approve/reject.

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

        // Catatan: Notifikasi master data pending approval tidak terhapus oleh clearAll,
        // karena item fisik di tabel items masih berstatus pending dan membutuhkan approval nyata.

        return response()->json(['success' => true, 'message' => 'Semua notifikasi berhasil dihapus']);
    }
}
