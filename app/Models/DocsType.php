<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocsType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function docs()
    {
        return $this->hasMany(Docs::class);
    }
}
