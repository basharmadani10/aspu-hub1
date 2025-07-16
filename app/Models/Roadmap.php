<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'specialization_id',
        'type',
    ];



public function subjects()
{
   
    return $this->belongsToMany(Subject::class, 'roadmap_subjects');
}


    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id', 'SpecializationID');
    }


}
