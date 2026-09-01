<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasDeleteNotification
{
    protected static function bootHasDeleteNotification()
    {
        static::deleted(function ($model) {
            try {
                $className = class_basename($model);

                $type = match ($className) {
                    'InProcessChecksheet'        => 'In Process',
                    'CrossCutChecksheet'         => 'Cross Cut',
                    'CrossCutPaintingChecksheet' => 'Cross Cut Painting',
                    'SortirChecksheet'           => 'Sortir',
                    'SubAssyChecksheet'          => 'Sub Assy',
                    'PlatingChecksheet'          => 'Plating',
                    'PaintingChecksheet'         => 'Painting',
                    'DoubleTapeChecksheet'       => 'Double Tape',
                    'FirstPieceApproval'         => 'First Piece Approval',
                    'IncomingSubPart'            => 'Incoming Sub-Part',
                    'IncomingPart'               => 'Incoming Part',
                    'IncomingMaterial'           => 'Incoming Material',
                    'IncomingExport'             => 'Incoming Export',
                    'IncomingChemical'           => 'Incoming Chemical',
                    default                      => null,
                };

                if (!$type || !$model->id) {
                    return;
                }

                // Gunakan raw query untuk memanfaatkan index `type` secara efisien.
                // JSON_EXTRACT jauh lebih cepat daripada Laravel's arrow notation
                // karena query builder kadang tidak menghasilkan indeks-friendly SQL.
                $checksheetId     = (string) $model->id;
                $checksheetIdInt  = (int)    $model->id;

                // Batasi window waktu ke 2 jam sejak checksheet dibuat (notifikasi NG
                // hampir selalu dibuat sesaat setelah checksheet disimpan).
                $createdAt = null;
                if (isset($model->created_at) && $model->created_at) {
                    $createdAt = $model->created_at->copy()->subMinutes(5)->toDateTimeString();
                }

                $sql = "DELETE FROM notifications
                        WHERE type IN ('ng_finding', 'rejection_alert')
                        " . ($createdAt ? "AND created_at >= ?" : "") . "
                        AND (
                            (JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_id'))   = ?
                             AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_type')) = ?)
                            OR
                            (JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_id'))   = ?
                             AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_type')) = ?)
                        )";

                $bindings = $createdAt
                    ? [$createdAt, $checksheetId, $type, $checksheetIdInt, $type]
                    : [$checksheetId, $type, $checksheetIdInt, $type];

                $deleted = DB::affectingStatement($sql, $bindings);

                if ($deleted > 0) {
                    Log::info("Deleted {$deleted} notifications for deleted checksheet: ID={$model->id}, Type={$type}");
                }
            } catch (\Exception $e) {
                Log::error('Error deleting notifications for deleted checksheet: ' . $e->getMessage());
            }
        });
    }
}
