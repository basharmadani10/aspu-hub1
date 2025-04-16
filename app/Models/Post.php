<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'typePost', 'community_id', 'positiveVotes', 'negativeVotes', 'user_id'
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
