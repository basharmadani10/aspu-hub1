<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'roleID'; // Custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['roleName', 'roleDescription'];

    // Relationship with RoleToPermission
    public function roletopermession()
    {
        return $this->hasMany(RoleToPermission::class, 'roleID');
    }

    public function users()
    {
        return $this->hasMany(User::class);}}
