<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'plate_number',
        'vehicle_type',
        'brand',
        'model',
        'vehicle_color',
        'or_path',
        'cr_path',
        'or_number',
        'cr_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userDetails()
    {
        return $this->belongsTo(UserDetails::class, 'user_details_id');
    }
}
