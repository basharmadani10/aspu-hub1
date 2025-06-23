<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscribe_Communities extends Model
{

    protected $fillable = [
        'community_id',
        'user_id',
    ];




    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function community()
    {
        return $this->belongsTo(Communitie::class, 'community_id');
    }









}
