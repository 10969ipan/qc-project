<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'item_change_histories';

    protected $fillable = [
        'item_id',
        'plant_id',
        'user_id',
        'field_name',
        'field_label',
        'before_value',
        'after_value',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')->withoutGlobalScope('plant');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
