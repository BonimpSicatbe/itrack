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

        // Handle colleges
        if (isset($assignedTo['selectAllColleges']) && $assignedTo['selectAllColleges']) {
            $parts[] = 'All Colleges';
        } elseif (isset($assignedTo['colleges']) && is_array($assignedTo['colleges']) && !empty($assignedTo['colleges'])) {
            $collegeNames = College::whereIn('id', $assignedTo['colleges'])
                ->pluck('name')
                ->toArray();
            if (!empty($collegeNames)) {
                $parts[] = 'Colleges: ' . implode(', ', $collegeNames);
            }
        }

        // Handle departments
        if (isset($assignedTo['selectAllDepartments']) && $assignedTo['selectAllDepartments']) {
            $parts[] = 'All Departments';
        } elseif (isset($assignedTo['departments']) && is_array($assignedTo['departments']) && !empty($assignedTo['departments'])) {
            $departmentNames = Department::whereIn('id', $assignedTo['departments'])
                ->pluck('name')
                ->toArray();
            if (!empty($departmentNames)) {
                $parts[] = 'Departments: ' . implode(', ', $departmentNames);
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
     * Check if a user belongs to this requirement's assigned colleges AND departments
     */
    public function isAssignedToUser(User $user): bool
    {
        $assignedTo = $this->assigned_to ?? [];
        
        $colleges = $assignedTo['colleges'] ?? [];
        $departments = $assignedTo['departments'] ?? [];
        $selectAllColleges = $assignedTo['selectAllColleges'] ?? false;
        $selectAllDepartments = $assignedTo['selectAllDepartments'] ?? false;

        // Check if user has college and department
        if (!$user->college_id || !$user->department_id) {
            return false;
        }

        // Convert user IDs to string for comparison (since JSON stores them as strings)
        $userCollegeId = (string)$user->college_id;
        $userDepartmentId = (string)$user->department_id;

        // Check college assignment - user's college must be in the colleges array OR selectAllColleges is true
        $collegeAssigned = $selectAllColleges || 
                          (is_array($colleges) && in_array($userCollegeId, $colleges));

        // Check department assignment - user's department must be in the departments array OR selectAllDepartments is true
        $departmentAssigned = $selectAllDepartments ||
                            (is_array($departments) && in_array($userDepartmentId, $departments));

        // User must belong to BOTH assigned college AND assigned department
        return $collegeAssigned && $departmentAssigned;
    }

    /**
     * Get all users assigned to this requirement
     */
    public function getAssignedUsers()
    {
        $assignedTo = $this->assigned_to ?? [];
        
        $colleges = $assignedTo['colleges'] ?? [];
        $departments = $assignedTo['departments'] ?? [];
        $selectAllColleges = $assignedTo['selectAllColleges'] ?? false;
        $selectAllDepartments = $assignedTo['selectAllDepartments'] ?? false;

        $query = User::query();

        if (!$selectAllColleges && !empty($colleges)) {
            $query->whereIn('college_id', $colleges);
        }

        if (!$selectAllDepartments && !empty($departments)) {
            $query->whereIn('department_id', $departments);
        }

        return $query->get();
    }

    // Legacy methods (keeping for backward compatibility)
    public function assignedTo()
    {
        return Requirement::where('assigned_to', $this->assigned_to);
    }

    public function assignedTargets()
    {
        if (College::where('name', $this->assigned_to)->exists()) {
            $college = College::where('name', $this->assigned_to)->first();
            return User::where('college_id', $college->id)->get();
        } elseif (Department::where('name', $this->assigned_to)->exists()) {
            $department = Department::where('name', $this->assigned_to)->first();
            return User::where('department_id', $department->id)->get();
        }

        return collect();
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

    public function assignedToType()
    {
        if ($this->target === 'college') {
            return College::find($this->target_id);
        } elseif ($this->target === 'department') {
            return Department::find($this->target_id);
        }
        return null;
    }

    public function targetUsers()
    {
        if ($this->target === 'college') {
            return User::where('college_id', $this->target_id)->get();
        } elseif ($this->target === 'department') {
            return User::where('department_id', $this->target_id)->get();
        }
        return collect();
    }

    public function isOverdue(): bool
    {
        return Carbon::now()->gt($this->due);
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