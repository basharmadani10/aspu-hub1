<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Communitie;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'typePost', 'community_id', 'positiveVotes', 'negativeVotes', 'user_id', 'tags'
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


    public function postVotes(): HasMany
    {
        return $this->hasMany(PostVote::class);
    }


    public function community()
{
    return $this->belongsTo(Communitie::class, 'community_id');


}


public function reports()
{
    return $this->morphMany(Report::class, 'reportable');
}




}



