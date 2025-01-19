<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/Vote.php
class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'user_id',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

