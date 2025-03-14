<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'description',
        'is_for_university',
    ];

    public function userSemesters()
    {
        return $this->hasMany(UserSemester::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
