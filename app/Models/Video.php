<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    protected $fillable = ['video', 'post_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
