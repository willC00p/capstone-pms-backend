<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $table = 'admin';

    protected $fillable = ['user_id', 'report_id', 'feedback_id', 'guard_id', 'role'];

    protected $hidden = ['remember_token'];
}
