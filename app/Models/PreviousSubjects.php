<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreviousSubjects extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_subject_id',
        'previous_subject_id',
    ];

    public function userSubject()
    {
        return $this->belongsTo(UserSubject::class);
    }
}
