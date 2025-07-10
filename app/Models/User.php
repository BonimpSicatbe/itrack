<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Requirement;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'extensionname',
        'email',
        'email_verified_at',
        'department_id',
        'college_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========== ========== RELATIONSHIPS | START ========== ==========
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class, 'id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function submittedRequirements()
    {
        return $this->hasMany(SubmittedRequirement::class, 'user_id', 'id');
    }
    // ========== ========== RELATIONSHIPS | END ========== ==========


    // ========== ========== ACCESSORS | START ========== ==========
    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->middlename} {$this->lastname} {$this->extensionname}");
    }
    // ========== ========== ACCESSORS | END ========== ==========


}
