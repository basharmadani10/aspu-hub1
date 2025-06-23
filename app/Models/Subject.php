<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hour_count',
        'Description',
        'paraticalMark',
        'abstractMark',
        'SpecializationID',
        'status'
    ];

    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'SpecializationID');
    }


    public function userSubjects()
    {
        return $this->hasMany(UserSubject::class, 'subjectID');
    }


    public function requiredPrerequisites()
    {
        return $this->belongsToMany(Subject::class, 'previous_subjects', 'subjectID', 'PreviousSubjectID');
    }


    public function isPrerequisiteFor()
    {
        return $this->belongsToMany(Subject::class, 'previous_subjects', 'PreviousSubjectID', 'subjectID');
    }

}
