<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firstname','lastname',
        'nationality','address','municipality','provice','country','zip_code',
        'father_firstname','father_lastname',
        'mother_firstname','mother_lastname',
        'spouse_firstname','spouse_lastname'
    ];
}
