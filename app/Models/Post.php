<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'location_type',
        'location_id',
    ];
    public function location()
    {
        return $this->morphTo();
    }
}

