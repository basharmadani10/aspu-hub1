<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docs extends Model
{
    use HasFactory;

    protected $primaryKey = 'DocID';
 
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'docs_type_id',
        'subject_id',
        'doc_name',
        'doc_url',
    ];

    public function docsType()
    {
        return $this->belongsTo(DocsType::class, 'docs_type_id');
    }


    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }


}
