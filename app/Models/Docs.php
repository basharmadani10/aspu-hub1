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
        'subject_id', // أضفنا هذا
        'doc_name',   // أضفنا هذا
        'doc_url',
    ];

    public function docsType()
    {
        return $this->belongsTo(DocsType::class, 'docs_type_id');
    }

    // علاقة مباشرة مع Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // إذا كنت ترغب في معرفة من رفع المستند مستقبلاً، يمكنك إضافة حقل user_id في الهجرة
    // وعلاقة هنا:
    // public function uploader()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }
}
