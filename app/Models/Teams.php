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
        'user_id',
        'name',
        'personal_team',
    ];

    public function team_user()
    {
        return $this->hasMany(TeamUser::class, 'team_id', 'id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user', 'user_id');
    }

    public function leads()
    {
        return $this->belongsToMany(User::class, 'team_user', 'user_id', 'lead_id');
    }
}
