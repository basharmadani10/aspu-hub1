<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community_Manager extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'community_id', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function community()
    {
        return $this->belongsTo(Communitie::class, 'community_id');
    }
}
