<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    use HasFactory;

    // Add fillable fields if needed
    protected $fillable = [
        'email',
        'code',
        'expires_at',
    ];
}
