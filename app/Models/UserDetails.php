<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firstname', 'middlename', 'lastname', 
        'email', 'dob', 'gender', 'civil_status', 
        'nationality', 'religion', 'place_of_birth', 
        'address', 'municipality', 'provice', 'country', 'zip_code', 
        'fb_account_name', 
        'father_firstname', 'father_middleinitial', 'father_lastname', 
        'mother_firstname', 'mother_middleinitial', 'mother_lastname', 
        'spouse_firstname', 'spouse_middleinitial', 'spouse_lastname', 
        'no_of_children', 'source_of_income', 'work_description', 'id_card_presented', 'membership_date', 
        'profile_photo_path',
    ];
}
