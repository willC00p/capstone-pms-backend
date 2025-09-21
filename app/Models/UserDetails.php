<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firstname', 'lastname',
        'department', 'contact_number', 'plate_number',
        'student_no', 'course', 'yr_section',
        'faculty_id', 'employee_id',
        'position',
        'or_path',
        'cr_path',
        'from_pending',
    ];

    protected $casts = [
        'plate_numbers' => 'array',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'user_details_id');
    }

    public function addPlateNumber(string $plate)
    {
        $plates = $this->plate_numbers ?? [];
        if (!in_array($plate, $plates)) {
            $plates[] = $plate;
            $this->plate_numbers = $plates;
            $this->save();
        }
    }
}
