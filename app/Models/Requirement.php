<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Requirement extends Model implements HasMedia
{
    use InteractsWithMedia, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'due',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'updated_by',
        'archived_by',
        'semester_id',
        'requirement_type_ids',
        'requirement_group',
    ];

    protected $casts = [
        'due' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'requirement_type_ids' => 'array',
        'assigned_to' => 'array', // Add this cast for JSON data
    ];

    protected $appends = ['assigned_to_display'];

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('guides')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile();

        $this->addMediaCollection('submissions')
            ->useDisk(config('media-library.disk_name'));
    }

    // Custom accessor for assigned_to to ensure proper JSON handling
    public function getAssignedToAttribute($value)
    {
        // If it's already an array, return it (this happens due to the cast)
        if (is_array($value)) {
            return $value;
        }
        
        // If it's null or empty, return empty array
        if (empty($value)) {
            return [];
        }
        
        // Try to decode JSON (for legacy data or when cast is not used)
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            
            // If JSON decode failed, return empty array
            if (json_last_error() !== JSON_ERROR_NONE) {
                logger("Failed to decode assigned_to for requirement {$this->id}: " . $value);
                return [];
            }
            
            return $decoded ?? [];
        }
        
        // Fallback for any other data type
        return [];
    }

    /**
     * Get formatted assigned_to display text
     */
    public function getAssignedToDisplayAttribute()
    {
        $assignedTo = $this->assigned_to;
        
        if (!$assignedTo || !is_array($assignedTo)) {
            return 'Not assigned';
        }

        $parts = [];

        // Handle programs
        if (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms']) {
            $parts[] = 'All Programs';
        } elseif (isset($assignedTo['programs']) && is_array($assignedTo['programs']) && !empty($assignedTo['programs'])) {
            $programNames = Program::whereIn('id', $assignedTo['programs'])
                ->get()
                ->map(function ($program) {
                    return $program->program_code . ' - ' . $program->program_name;
                })
                ->toArray();
                
            if (!empty($programNames)) {
                $parts[] = 'Programs: ' . implode(', ', $programNames);
            }
        }

        return !empty($parts) ? implode('; ', $parts) : 'Not assigned';
    }

    public function requirementTypes()
    {
        if (empty($this->requirement_type_ids)) {
            return collect();
        }
        
        return RequirementType::whereIn('id', $this->requirement_type_ids)->get();
    }

    public function hasRequirementType($typeId)
    {
        if (empty($this->requirement_type_ids)) {
            return false;
        }
        
        return in_array($typeId, $this->requirement_type_ids);
    }

    /**
     * Check if this requirement is part of a partnership group
     */
    public function isPartOfPartnership()
    {
        return in_array($this->requirement_group, ['midterm_assessment', 'finals_assessment']);
    }

    /**
     * Get partnership group name for display
     */
    public function getPartnershipGroupName()
    {
        return match($this->requirement_group) {
            'midterm_assessment' => 'Midterm Assessment',
            'finals_assessment' => 'Finals Assessment',
            default => null
        };
    }

    /**
     * Get partner requirements (TOS and Examinations in the same group)
     */
    public function getPartnerRequirements($semesterId = null)
    {
        if (!$this->isPartOfPartnership()) {
            return collect();
        }

        return Requirement::where('requirement_group', $this->requirement_group)
            ->where('id', '!=', $this->id)
            ->where('semester_id', $semesterId ?? $this->semester_id)
            ->get();
    }

    /**
     * Check if all partners in the group are submitted for a user and course
     */
    public function areAllPartnersSubmitted($userId = null, $courseId = null)
    {
        if (!$this->isPartOfPartnership()) {
            return true; // Not part of partnership, so considered "complete"
        }

        $userId = $userId ?? Auth::id();
        $courseId = $courseId ?? request()->input('selectedCourse');

        if (!$courseId) {
            return false;
        }

        $partners = $this->getPartnerRequirements();
        
        // Check if this requirement and all partners have submissions
        $allRequirements = $partners->push($this);
        
        foreach ($allRequirements as $requirement) {
            $hasSubmission = SubmittedRequirement::where('requirement_id', $requirement->id)
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->exists();
                
            if (!$hasSubmission) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if any partner in the group is submitted for a user and course
     */
    public function isAnyPartnerSubmitted($userId = null, $courseId = null)
    {
        if (!$this->isPartOfPartnership()) {
            return false;
        }

        $userId = $userId ?? Auth::id();
        $courseId = $courseId ?? request()->input('selectedCourse');

        if (!$courseId) {
            return false;
        }

        $partners = $this->getPartnerRequirements();
        
        foreach ($partners as $partner) {
            $hasSubmission = SubmittedRequirement::where('requirement_id', $partner->id)
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->exists();
                
            if ($hasSubmission) {
                return true;
            }
        }

        return false;
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();

        $this
            ->addMediaConversion('thumb')
            ->width(100)
            ->height(100);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    // ========== Relationships ==========
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(SubmittedRequirement::class);
    }

    public function userSubmissions($userId = null): HasMany
    {
        $userId = $userId ?? Auth::id();
        return $this->hasMany(SubmittedRequirement::class)
            ->where('user_id', $userId)
            ->with(['media', 'reviewer'])
            ->latest();
    }

    public function submittedRequirements(): HasMany
    {
        return $this->hasMany(SubmittedRequirement::class);
    }

    public function getSubmittedRequirementsCountAttribute()
    {
        if (!$this->relationLoaded('submittedRequirements')) {
            return $this->submittedRequirements()->count();
        }
        
        return $this->submittedRequirements->count();
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function guides(): MorphMany
    {
        return $this->media()->where('collection_name', 'guides');
    }

    // ========== Methods ==========

    /**
     * Check if a user belongs to this requirement's assigned programs
     */
    public function isAssignedToUser(User $user): bool
    {
        $assignedTo = $this->assigned_to ?? [];
        
        $programs = $assignedTo['programs'] ?? [];
        $selectAllPrograms = $assignedTo['selectAllPrograms'] ?? false;

        // Check if user has a program
        if (!$user->program_id) {
            return false;
        }

        // Convert user program ID to string for comparison (since JSON stores them as strings)
        $userProgramId = (string)$user->program_id;

        // Check program assignment - user's program must be in the programs array OR selectAllPrograms is true
        return $selectAllPrograms || 
               (is_array($programs) && in_array($userProgramId, $programs));
    }

    /**
     * Get all users assigned to this requirement
     */
    public function getAssignedUsers()
    {
        $assignedTo = $this->assigned_to ?? [];
        
        $programs = $assignedTo['programs'] ?? [];
        $selectAllPrograms = $assignedTo['selectAllPrograms'] ?? false;

        $query = User::query();

        if (!$selectAllPrograms && !empty($programs)) {
            $query->whereIn('program_id', $programs);
        }

        return $query->get();
    }

    // Legacy methods (keeping for backward compatibility)
    public function assignedTo()
    {
        return Requirement::where('assigned_to', $this->assigned_to);
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'completed' => 'success',
            'archived' => 'neutral',
        ][$this->status] ?? 'neutral';
    }

    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'info',
            'normal' => 'warning',
            'high' => 'error',
        ][$this->priority] ?? 'neutral';
    }

    public function isOverdue(): bool
    {
        return Carbon::now()->gt($this->due);
    }

    public function submissionIndicators()
    {
        return $this->hasMany(RequirementSubmissionIndicator::class);
    }

    protected static function booted()
    {
        static::deleting(function ($requirement) {
            // Delete related media (guides)
            $requirement->media()->delete();
            
            // Delete related submissions and their media
            $requirement->submissions()->each(function ($submission) {
                $submission->delete();
            });
            
            // Delete both types of related notifications
            DB::table('notifications')
                ->where(function ($query) use ($requirement) {
                    $query->where('type', 'App\Notifications\NewSubmissionNotification')
                          ->where('data->requirement_id', $requirement->id);
                })
                ->orWhere(function ($query) use ($requirement) {
                    $query->where('type', 'App\Notifications\NewRequirementNotification')
                          ->where('data->requirement_id', $requirement->id);
                })
                ->delete();
        });
    }
}