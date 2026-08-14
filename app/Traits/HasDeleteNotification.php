<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

trait HasDeleteNotification
{
    protected static function bootHasDeleteNotification()
    {
        static::deleted(function ($model) {
            try {
                // Determine checksheet type based on the class basename
                $className = class_basename($model);
                
                // Map model class name to notification checksheet type if needed
                $type = null;
                switch ($className) {
                    case 'InProcessChecksheet':
                        $type = 'In Process';
                        break;
                    case 'CrossCutChecksheet':
                        $type = 'Cross Cut';
                        break;
                    case 'CrossCutPaintingChecksheet':
                        $type = 'Cross Cut Painting';
                        break;
                    case 'SortirChecksheet':
                        $type = 'Sortir';
                        break;
                    case 'SubAssyChecksheet':
                        $type = 'Sub Assy';
                        break;
                    case 'PlatingChecksheet':
                        $type = 'Plating';
                        break;
                    case 'PaintingChecksheet':
                        $type = 'Painting';
                        break;
                    case 'DoubleTapeChecksheet':
                        $type = 'Double Tape';
                        break;
                    case 'FirstPieceApproval':
                        $type = 'First Piece Approval';
                        break;
                    case 'IncomingSubPart':
                        $type = 'Incoming Sub-Part';
                        break;
                    case 'IncomingPart':
                        $type = 'Incoming Part';
                        break;
                    case 'IncomingMaterial':
                        $type = 'Incoming Material';
                        break;
                    case 'IncomingExport':
                        $type = 'Incoming Export';
                        break;
                    case 'IncomingChemical':
                        $type = 'Incoming Chemical';
                        break;
                }

                if ($type) {
                    $deleted = Notification::whereIn('type', ['ng_finding', 'rejection_alert'])
                        ->where(function ($query) use ($model) {
                            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_id')) = ?", [(string) $model->id])
                                  ->orWhereRaw("JSON_EXTRACT(data, '$.checksheet_id') = ?", [$model->id]);
                        })
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_type')) = ?", [$type])
                        ->delete();
                    
                    Log::info("Deleted {$deleted} notifications for deleted checksheet: ID={$model->id}, Type={$type}");
                }
            } catch (\Exception $e) {
                Log::error('Error deleting notifications for deleted checksheet: ' . $e->getMessage());
            }
        });
    }
}
