<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleToPermission extends Model
{
    use HasFactory;

    protected $fillable = ['roleID', 'permissionID'];

    // Belongs to Permission
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissionID');
    }

    // Belongs to Role (assuming there's a Role model)
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleID');
    }
}
