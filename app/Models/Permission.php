<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $primaryKey = 'permissionID'; // Custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['permissionName', 'permissionDescription'];

    // Relationship with RoleToPermission
    public function roles()
    {
        return $this->hasMany(RoleToPermission::class, 'permissionID');
    }
}
