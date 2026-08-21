<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingPartArrivalLog extends Model
{
    use HasFactory;

    protected $table = 'incoming_part_arrival_logs';

    protected $fillable = [
        'arrival_id',
        'plant_id',
        'user_id',
        'user_name',
        'item_name',
        'part_number',
        'tanggal_datang',
        'shift_datang',
        'action_type',
        'qty_before',
        'qty_change',
        'qty_after',
        'description',
    ];

    protected $casts = [
        'tanggal_datang' => 'date:Y-m-d',
        'qty_before'     => 'integer',
        'qty_change'     => 'integer',
        'qty_after'      => 'integer',
    ];

    public function arrival()
    {
        return $this->belongsTo(IncomingPartArrival::class, 'arrival_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    /**
     * Record a stock log entry.
     */
    public static function record($arrival, string $actionType, int $qtyBefore, int $qtyChange, int $qtyAfter, ?string $description = null)
    {
        $user = auth()->user();
        $userName = $user ? ($user->name ?? $user->username ?? 'User #' . $user->id) : 'System';

        $itemName = '-';
        $partNumber = '-';
        $tglDatang = null;
        $shiftDatang = null;
        $plantId = null;

        if ($arrival) {
            $plantId = $arrival->plant_id;
            $tglDatang = $arrival->tanggal_datang;
            $shiftDatang = $arrival->shift_datang;
            if ($arrival->item) {
                $itemName = $arrival->item->name;
                $partNumber = $arrival->item->part_number ?? '-';
            } elseif (isset($arrival->item_name)) {
                $itemName = $arrival->item_name;
                $partNumber = $arrival->part_number ?? '-';
            }
        }

        if (!$plantId && $user) {
            $plantId = $user->plant_id ?? null;
        }

        $plantId = $plantId ? (string)$plantId : null;

        return self::create([
            'arrival_id'     => $arrival ? $arrival->id : null,
            'plant_id'       => $plantId,
            'user_id'        => $user ? $user->id : null,
            'user_name'      => $userName,
            'item_name'      => $itemName,
            'part_number'    => $partNumber,
            'tanggal_datang' => $tglDatang,
            'shift_datang'   => $shiftDatang,
            'action_type'    => strtoupper($actionType),
            'qty_before'     => $qtyBefore,
            'qty_change'     => $qtyChange,
            'qty_after'      => $qtyAfter,
            'description'    => $description,
        ]);
    }
}
