<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'reason', 'reportable_id', 'reportable_type'];


    public function reportable()
{
    return $this->morphTo();
}

public function reporter()
{
    return $this->belongsTo(User::class, 'user_id');
}
}

