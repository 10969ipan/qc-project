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

        // 2. Notifikasi Transaksional / Temuan NG / Reminder Kalibrasi dari tabel notifications
        $sevenDaysAgo = now()->subDays(7);
        $notifQuery = Notification::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->where('type', '!=', 'master_item_approval')
            ->unread()
            ->where('created_at', '>=', $sevenDaysAgo);

        $hasNotifPlantCol = \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'notif_plant_id');
        if ($user->role !== 'admin') {
            $notifQuery->where(function ($q) use ($user, $hasNotifPlantCol) {
                if ($hasNotifPlantCol) {
                    $q->where('notif_plant_id', $user->plant_id)
                        ->orWhereNull('notif_plant_id');
                } else {
                    $q->whereRaw("JSON_EXTRACT(data, '$.plant_id') = ?", [$user->plant_id])
                        ->orWhereRaw("JSON_EXTRACT(data, '$.plant_id') IS NULL");
                }
            });
        }

        $regularNotifications = $notifQuery->orderBy('created_at', 'desc')->limit(20)->get();

        // Format link URL untuk notifikasi checksheet
        $regularNotifications->transform(function ($notification) {
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
                    case 'Outgoing Export':
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
                    $basePath = request()->getBaseUrl();
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

        $regularUnreadCount = (clone $notifQuery)->select('id')->limit(10)->get()->count();

        // Gabungkan dan urutkan notifikasi terbaru
        $mergedNotifications = $notifications->concat($regularNotifications)
            ->sortByDesc(function ($item) {
                return $item->created_at ?? now();
            })
            ->values()
            ->take(20);

        return response()->json([
            'notifications' => $mergedNotifications,
            'unread_count' => $unreadCount + $regularUnreadCount
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
