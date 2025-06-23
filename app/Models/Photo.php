<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
class Photo extends Model
{
    use HasFactory;

    protected $fillable = ['photo', 'post_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function getPhotoAttribute($value)
    {
        
        return $value ? Storage::url($value) : null;
    }
}