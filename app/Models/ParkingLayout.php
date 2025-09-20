<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'background_image',
        'layout_data'
    ];

    protected $casts = [
        'layout_data' => 'json'  // Changed from 'array' to 'json' for better handling
    ];

    /**
     * Get the layout data attribute.
     *
     * @param  mixed  $value
     * @return array
     */
    public function getLayoutDataAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return is_array($value) ? $value : [];
    }

    /**
     * Set the layout data attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setLayoutDataAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['layout_data'] = json_encode($decoded);
                return;
            }
        }
        $this->attributes['layout_data'] = is_array($value) ? json_encode($value) : json_encode([]);
    }

    public function parkingSlots()
    {
        return $this->hasMany(ParkingSlot::class, 'layout_id');
    }
}
