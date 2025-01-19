<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubject extends Model
{
    use HasFactory;


    protected $fillable = [
        'specialization_id',
        'name',
        'hours_count',
        'description',
        'partical_mark',
        'abstract_mark',
    ];

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function userSubjects()
    {
        return $this->hasMany(UserSubject::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function previousSubjects()
    {
        return $this->hasMany(PreviousSubjects::class);
    }

    public function docs()
    {
        return $this->hasMany(Docs::class);
    }



}
