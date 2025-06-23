<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    // NEW: تحديد المفتاح الأساسي للجدول
    protected $primaryKey = 'SpecializationID';
    // NEW: تأكيد أن المفتاح الأساسي يتزايد تلقائياً (إذا كان كذلك في قاعدة البيانات)
    public $incrementing = true;
    // NEW: تحديد نوع المفتاح الأساسي (إذا كان غير int)
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'description',
        'is_for_university',
    ];

    public function userSemesters()
    {
        // استخدام المفتاح المحلي Explicitly لتجنب الالتباس إذا كان مختلفًا عن primaryKey الافتراضي
        return $this->hasMany(UserSemester::class, 'SpecializationID', 'SpecializationID');
    }

    public function subjects()
    {
        // استخدام المفتاح المحلي Explicitly لتجنب الالتباس إذا كان مختلفًا عن primaryKey الافتراضي
        return $this->hasMany(Subject::class, 'SpecializationID', 'SpecializationID');
    }
}
