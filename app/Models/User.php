<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

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
        'college_id',
        'is_active',
        'deactivated_at',
        'password',
        'position',
        'teaching_started_at',
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
        'is_active' => 'boolean', // NEW
        'deactivated_at' => 'datetime', // NEW
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

    // ==================== COURSE RELATIONSHIPS ====================

    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class, 'professor_id');
    }

    public function taughtCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_assignments', 'professor_id', 'course_id')
            ->as('assignment')
            ->using(CourseAssignment::class)
            ->withPivot('assignment_id', 'year', 'semester', 'assignment_date')
            ->withTimestamps();
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'submitted_requirements', 'user_id', 'course_id')
            ->distinct();
    }

    public function submissionIndicators()
    {
        return $this->hasMany(RequirementSubmissionIndicator::class);
    }

    // ==================== EXISTING RELATIONSHIPS ====================

    public function semester()
    {
        return $this->belongsToMany(Semester::class, 'user_semester', 'user_id', 'semester_id');
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class)->withDefault([
            'name' => 'N/A',
        ]);
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

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class, 'professor_id');
    }

    public function r()
    {
        $currentSemester = Semester::where('is_active', true)->first();

        if (!$currentSemester) {
            return collect();
        }

        return $this->courseAssignments()
            ->where('semester_id', $currentSemester->id)
            ->with('course.program')
            ->get()
            ->pluck('course');
    }

    // ==================== ACCESSORS ====================

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getFullNameAttribute(): string
    {
        $nameParts = [
            $this->firstname,
            $this->formatMiddleName($this->middlename),
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

    public function getCollegeNameAttribute(): string
    {
        return $this->college->name ?? 'N/A';
    }

    // ==================== HELPER METHODS ====================

    /**
     * Format middle name to show only first initial with period
     * Example: "Tan" becomes "T."
     */
    protected function formatMiddleName(?string $middleName): string
    {
        if (empty($middleName)) {
            return '';
        }

        // Get first character and add period
        return substr(trim($middleName), 0, 1) . '.';
    }

    // ==================== SCOPES ====================

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('firstname', 'like', "%{$search}%")
                ->orWhere('middlename', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeByCollege($query, int $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    public function scopeActive($query) // NEW
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query) // NEW
    {
        return $query->where('is_active', false);
    }

    // ==================== MEDIA ====================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_picture')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);
    }
}
