<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubject extends Model
{
    use HasFactory;


    protected $fillable = [
        'userID',
        'subjectID',
        'semesterID',
        'has_been_finished',
        'has_been_canceled',
        'mark',
        'hour_count'
    ];

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }




    public function user()
    {
        return $this->belongsTo(User::class, 'userID' );
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subjectID');
    }



    public function semester()
    {
        return $this->belongsTo(UserSemester::class, 'semesterID');
    }

    public function docs()
    {
        return $this->hasMany(Docs::class);
    }



}
