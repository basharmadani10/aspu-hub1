<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docs extends Model
{
    use HasFactory;

    protected $fillable = [
        'docs_type_id',
        'user_subject_id',
        'doc_name',
        'doc_url',
    ];

    public function docsType()
    {
        return $this->belongsTo(DocsType::class);
    }

    public function userSubject()
    {
        return $this->belongsTo(UserSubject::class);
    }
}
