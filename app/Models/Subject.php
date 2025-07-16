<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Docs;

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
        'status',
        'references'
    ];



    protected $casts = [
        'references' => 'array', 
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



    public function docs()
    {
        // Change Doc::class to Docs::class
        return $this->hasMany(Docs::class, 'subject_id'); // <--- CORRECTED: Using Docs::class and 'subject_id' as foreign key
    }
}
