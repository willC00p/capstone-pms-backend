<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')->wherePivot('role', 'MEMBER');
    }

    public function leads()
    {
        return $this->belongsToMany(User::class, 'team_user')->wherePivot('role', 'LEAD');
    }
}
