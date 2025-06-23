<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communitie extends Model
{
    use HasFactory;


    protected $table = 'communities';


    protected $fillable = [
        'name',
        'image',
        'cover_image',
        'subscriber_count',
    ];


    public function managers()
    {
        return $this->hasMany(Community_Manager::class, 'community_id');
    }


    public function subscribers()
    {
        return $this->hasMany(Subscribe_Communities::class, 'community_id');
    }


    public function posts()
    {
        return $this->morphMany(Post::class, 'location');
    }
}
