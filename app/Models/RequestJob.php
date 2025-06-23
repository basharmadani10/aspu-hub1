<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RequestJob extends Model
{
    use HasFactory;
    protected $table = 'request_jobs';
    protected $fillable = [
        'first_name', 'last_name', 'email', 'doc_url', 'is_accepted',
    ];
    protected $casts = [
        'is_accepted' => 'boolean',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
