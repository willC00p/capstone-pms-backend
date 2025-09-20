<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_slot_id',
        'user_id',
        'guest_name',
        'guest_contact',
        'vehicle_plate',
        'vehicle_type',
        'vehicle_color',
        'start_time',
        'end_time',
        'status',
        'purpose',
        'faculty_position',
        'assignee_type',
        'assignment_type'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function parkingSlot()
    {
        return $this->belongsTo(ParkingSlot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
