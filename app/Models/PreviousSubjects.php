<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreviousSubjects extends Model
{
    use HasFactory;

   
    protected $fillable = [
        'subjectID',
        'PreviousSubjectID',
    ];


    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subjectID');
    }


    public function prerequisite()
    {
        return $this->belongsTo(Subject::class, 'PreviousSubjectID');
    }
}

