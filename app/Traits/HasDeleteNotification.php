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
                $className = class_basename($model);
                
                $type = match ($className) {
                    'InProcessChecksheet' => 'In Process',
                    'CrossCutChecksheet' => 'Cross Cut',
                    'CrossCutPaintingChecksheet' => 'Cross Cut Painting',
                    'SortirChecksheet' => 'Sortir',
                    'SubAssyChecksheet' => 'Sub Assy',
                    'PlatingChecksheet' => 'Plating',
                    'PaintingChecksheet' => 'Painting',
                    'DoubleTapeChecksheet' => 'Double Tape',
                    'FirstPieceApproval' => 'First Piece Approval',
                    'IncomingSubPart' => 'Incoming Sub-Part',
                    'IncomingPart' => 'Incoming Part',
                    'IncomingMaterial' => 'Incoming Material',
                    'IncomingExport' => 'Incoming Export',
                    'IncomingChemical' => 'Incoming Chemical',
                    default => null,
                };

                if ($type && $model->id) {
                    $query = Notification::whereIn('type', ['ng_finding', 'rejection_alert']);

                    if (isset($model->created_at) && $model->created_at) {
                        $query->where('created_at', '>=', $model->created_at->copy()->subMinutes(5));
                    }

                    $deleted = $query->where(function ($q) use ($model, $type) {
                        $q->where(function ($sub) use ($model, $type) {
                            $sub->where('data->checksheet_id', $model->id)
                                ->where('data->checksheet_type', $type);
                        })->orWhere(function ($sub) use ($model, $type) {
                            $sub->where('data->checksheet_id', (string) $model->id)
                                ->where('data->checksheet_type', $type);
                        });
                    })->delete();
                    
                    Log::info("Deleted {$deleted} notifications for deleted checksheet: ID={$model->id}, Type={$type}");
                }
            } catch (\Exception $e) {
                Log::error('Error deleting notifications for deleted checksheet: ' . $e->getMessage());
            }
        });
    }
}
