<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    protected $fillable = [
        'layout_id',
        'space_number',
        'space_type',
        'space_status',
        'position_x',
        'position_y',
        'width',
        'height',
        'rotation',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'position_x' => 'float',
        'position_y' => 'float',
        'width' => 'float',
        'height' => 'float',
        'rotation' => 'float'
    ];

    public function layout()
    {
        return $this->belongsTo(ParkingLayout::class, 'layout_id');
    }
}
