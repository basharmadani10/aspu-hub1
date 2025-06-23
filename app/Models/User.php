<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;



    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'country',
        'current_location',
        'gender',
        'birth_date',
        'bio',
        'roleID',
        'email_verification_code',
        'is_blocked',
        'number_of_completed_hours'




    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_blocked' => 'boolean',


    ];

    public function managedCommunities()
    {
        return $this->hasMany(Community_Manager::class, 'user_id');
    }


    public function subscribedCommunities()
    {
        return $this->hasMany(Subscribe_Communities::class, 'user_id');
    }

    public function specializations()
    {
        return $this->hasManyThrough(
            Specialization::class,
            UserSemester::class,
            'userID', // Foreign key on UserSemester table
            'SpecializationID', // Foreign key on Specialization table
            'id', // Local key on User table
            'SpecializationID' // Local key on UserSemester table
        );
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleID');
    }



    public function requestJobs()
    {
        return $this->hasMany(RequestJob::class);
    }


    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function favoritePosts()
    {
        return $this->hasMany(FavoritePost::class);
    }

    public function userSemesters()
    {
        return $this->hasMany(UserSemester::class, 'userID');
    }

    public function userSubjects()
    {
        return $this->hasMany(UserSubject::class, 'userID');
    }


    public function previousSubjects()
    {
        return $this->hasMany(PreviousSubjects::class);
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'location');
    }

    public function Subscribe_Communities() {
    return $this->hasMany(Subscribe_Communities::class, 'user_id');
    }


    public function subscriptions()
    {
        return $this->hasMany(Subscribe_Communities::class, 'user_id');
    }


    public function isAdmin(): bool
    {

        return $this->roleID === 2;
    }


}

