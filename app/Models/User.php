<?php

namespace App\Models;

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
        'number_of_completed_hours',
        'image',
        'initial_subjects_configured', // **NEW: Add this field**
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_blocked' => 'boolean',
        'initial_subjects_configured' => 'boolean', // **NEW: Cast as boolean**
    ];

    public function managedCommunities()
    {
        return $this->hasMany(Community_Manager::class, 'user_id');
    }

    public function subscribedCommunities()
    {
        return $this->hasMany(Subscribe_Communities::class, 'user_id');
    }

    // The Many-to-Many relationship with Specialization via UserSemester
    public function specializations()
    {
        return $this->hasManyThrough(
            Specialization::class,
            UserSemester::class,
            'userID',         // Foreign key on user_semesters table
            'SpecializationID', // Foreign key on specializations table (or UserSemester points to it)
            'id',             // Local key on users table
            'SpecializationID'  // Local key on specializations table (if different from 'id')
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
        // Keep this if you have a separate 'previous_subjects' table.
        // If it's handled via 'user_subjects' with 'has_been_finished', this relation might be redundant.
        return $this->hasMany(PreviousSubjects::class);
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'location');
    }

    public function Subscribe_Communities()
    {
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
