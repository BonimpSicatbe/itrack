<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- NEW: Added for many-to-many relationship
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;   
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    // ... (Your existing properties: fillable, hidden, casts, appends) ...
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'formatted_name',
        'name',
    ];

    // ==================== NEW COURSE RELATIONSHIPS ====================

    /**
     * Get all the individual course assignment records (historical and current).
     * The foreign key is 'professor_id' in the course_assignments table.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class, 'professor_id');
    }

    /**
     * Get all the courses the professor has taught (historical and current).
     * This uses the many-to-many relationship with the custom pivot model.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_assignments', 'id', 'course_id')
                    ->as('assignment') // Rename pivot relationship for clarity
                    ->using(CourseAssignment::class) // Specify the custom pivot model
                    // Include the historical/semester fields from the pivot table
                    ->withPivot('assignment_id', 'year', 'semester', 'assignment_date')
                    ->withTimestamps();
    }


    // ==================== EXISTING RELATIONSHIPS ====================

    public function semester() {
        return $this->belongsToMany(Semester::class, 'user_semester', 'user_id', 'semester_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withDefault([
            'name' => 'N/A',
        ]);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class)->withDefault([
            'name' => 'N/A',
        ]);
    }

    public function requirements()
    {
        return \App\Models\Requirement::where(function ($query) {
            if ($this->college) {
                $query->orWhere('assigned_to', $this->college->name);
            }

            if ($this->department) {
                $query->orWhere('assigned_to', $this->department->name);
            }
        });
    }

    public function createdRequirements(): HasMany
    {
        return $this->hasMany(Requirement::class, 'creator_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function submittedRequirements(): HasMany
    {
        return $this->hasMany(SubmittedRequirement::class);
    }

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')->latest();
    }

    // ==================== ACCESSORS ====================

    public function getNameAttribute(): string
    {
        return $this->full_name; // or any other logic you prefer
    }

    public function getFullNameAttribute(): string
    {
        $nameParts = [
            $this->firstname,
            $this->middlename,
            $this->lastname,
            $this->extensionname,
        ];

        return trim(implode(' ', array_filter($nameParts)));
    }

    public function getFormattedNameAttribute(): string
    {
        $name = $this->firstname;

        if ($this->middlename) {
            $name .= ' ' . substr($this->middlename, 0, 1) . '.';
        }

        $name .= ' ' . $this->lastname;

        if ($this->extensionname) {
            $name .= ' ' . $this->extensionname;
        }

        return $name;
    }

    public function getDepartmentNameAttribute(): string
    {
        return $this->department->name ?? 'N/A';
    }

    public function getCollegeNameAttribute(): string
    {
        return $this->college->name ?? 'N/A';
    }

    // ==================== SCOPES ====================

    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('firstname', 'like', "%{$search}%")
              ->orWhere('middlename', 'like', "%{$search}%")
              ->orWhere('lastname', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByCollege($query, int $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    // ==================== MEDIA ====================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_picture')
             ->singleFile()
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class, 'professor_id');
    }
}