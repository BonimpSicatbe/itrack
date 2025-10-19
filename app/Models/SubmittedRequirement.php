<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SubmittedRequirement extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'requirement_id',
        'user_id',
        'course_id',    
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'submitted_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // Status Constants
    const STATUS_UPLOADED = 'uploaded'; // New default status
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REVISION_NEEDED = 'revision_needed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPROVED = 'approved';

    /* ========== FONT AWESOME FILE ICONS WITH COLORS ========== */
    const FILE_ICONS = [
        'pdf' => ['icon' => 'fa-file-pdf', 'color' => 'text-red-500'],
        'doc' => ['icon' => 'fa-file-word', 'color' => 'text-blue-600'],
        'docx' => ['icon' => 'fa-file-word', 'color' => 'text-blue-600'],
        'txt' => ['icon' => 'fa-file-alt', 'color' => 'text-gray-500'],
        'rtf' => ['icon' => 'fa-file-alt', 'color' => 'text-gray-500'],
        'xls' => ['icon' => 'fa-file-excel', 'color' => 'text-green-600'],
        'xlsx' => ['icon' => 'fa-file-excel', 'color' => 'text-green-600'],
        'csv' => ['icon' => 'fa-file-csv', 'color' => 'text-green-500'],
        'ppt' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-500'],
        'pptx' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-500'],
        'jpg' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'jpeg' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'png' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'gif' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'bmp' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'svg' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'zip' => ['icon' => 'fa-file-archive', 'color' => 'text-yellow-600'],
        'rar' => ['icon' => 'fa-file-archive', 'color' => 'text-yellow-600'],
        '7z' => ['icon' => 'fa-file-archive', 'color' => 'text-yellow-600'],
        'tar' => ['icon' => 'fa-file-archive', 'color' => 'text-yellow-600'],
        'gz' => ['icon' => 'fa-file-archive', 'color' => 'text-yellow-600'],
        'mp4' => ['icon' => 'fa-file-video', 'color' => 'text-pink-500'],
        'mov' => ['icon' => 'fa-file-video', 'color' => 'text-pink-500'],
        'avi' => ['icon' => 'fa-file-video', 'color' => 'text-pink-500'],
        'wmv' => ['icon' => 'fa-file-video', 'color' => 'text-pink-500'],
        'mp3' => ['icon' => 'fa-file-audio', 'color' => 'text-indigo-500'],
        'wav' => ['icon' => 'fa-file-audio', 'color' => 'text-indigo-500'],
        'flac' => ['icon' => 'fa-file-audio', 'color' => 'text-indigo-500'],
        'default' => ['icon' => 'fa-file', 'color' => 'text-gray-400']
    ];

    /* ========== RELATIONSHIPS ========== */

    /**
     * The requirement this submission belongs to
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    /**
     * The course this submission belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The user who submitted this requirement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who reviewed this submission
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Media relationship for submission files
     */
    public function submissionFile(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', 'submission_files');
    }

    /**
     * Get the semester this submission belongs to through the requirement
     */
    public function semester()
    {
        return $this->hasOneThrough(
            Semester::class,
            Requirement::class,
            'id', // Foreign key on requirements table
            'id', // Foreign key on semesters table
            'requirement_id', // Local key on submitted_requirements table
            'semester_id' // Local key on requirements table
        );
    }

    /* ========== SCOPES ========== */

    /**
     * Scope for approved submissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for pending review submissions
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    /**
     * Scope for submissions needing revision
     */
    public function scopeNeedsRevision($query)
    {
        return $query->where('status', self::STATUS_REVISION_NEEDED);
    }

    /**
     * Scope for rejected submissions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope for uploaded submissions
     */
    public function scopeUploaded($query)
    {
        return $query->where('status', self::STATUS_UPLOADED);
    }

    /**
     * Scope for submissions that need to be moved to under review
     * (uploaded submissions that are tracked in indicator table)
     */
    public function scopeReadyForReview($query)
    {
        return $query->where('status', self::STATUS_UPLOADED)
            ->whereHas('submissionIndicator');
    }

    /**
     * Scope for submissions in active semester
     */
    public function scopeInActiveSemester($query)
    {
        return $query->whereHas('requirement.semester', function($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope for submissions in archived (inactive) semesters
     */
    public function scopeInArchivedSemester($query)
    {
        return $query->whereHas('requirement.semester', function($q) {
            $q->where('is_active', false);
        });
    }

    /**
     * Scope for submissions in a specific semester
     */
    public function scopeForSemester($query, $semesterId)
    {
        return $query->whereHas('requirement', function($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        });
    }

    /* ========== METHODS ========== */

    public static function statuses()
    {
        return [
            self::STATUS_UPLOADED => 'Uploaded',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_REVISION_NEEDED => 'Revision Required',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPROVED => 'Approved',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('submission_files')
            ->singleFile()
            ->useDisk('public');
    }

    public function getSubmissionFileAttribute()
    {
        return $this->submissionFile()->first();
    }

    public function addSubmissionFile($file)
    {
        $media = $this->addMedia($file)->toMediaCollection('submission_files');
        
        // Automatically update status to uploaded when file is added
        if ($this->status !== self::STATUS_UPLOADED) {
            $this->update(['status' => self::STATUS_UPLOADED]);
        }
        
        return $media;
    }

    public function getFileUrl()
    {
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        return $media->getUrl();
    }

    public function getFilePath()
    {
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        return $media->getPath();
    }

    public function deleteFile()
    {
        $media = $this->getFirstMedia('submission_files');
        if ($media) {
            $media->delete();
            return true;
        }
        return false;
    }

    public function canBeDeletedBy($user)
    {
        // Check user ownership
        $isOwner = $user->id === $this->user_id;
        
        // Check if file is from active semester
        $isActiveSemester = $this->requirement && 
                        $this->requirement->semester && 
                        $this->requirement->semester->is_active;
        
        // Check if file has uploaded status
        $isUploadedStatus = $this->status === self::STATUS_UPLOADED;
        
        return $isOwner && $isActiveSemester && $isUploadedStatus;
    }

    /**
     * Check if this submission should be moved to under review
     * Based on being uploaded AND stored in requirement submission indicator table
     */
    public function shouldBeMovedToUnderReview()
    {
        return $this->status === self::STATUS_UPLOADED && 
               $this->isTrackedInSubmissionIndicator();
    }

    /**
     * Move this submission from uploaded to under review status
     */
    public function moveToUnderReview()
    {
        if ($this->shouldBeMovedToUnderReview()) {
            return $this->update(['status' => self::STATUS_UNDER_REVIEW]);
        }
        return false;
    }

    /**
     * Check if this submission is tracked in the requirement submission indicator table
     */
    public function isTrackedInSubmissionIndicator()
    {
        if (!$this->relationLoaded('submissionIndicator')) {
            return $this->submissionIndicator()->exists();
        }
        
        return !is_null($this->submissionIndicator);
    }

    /* ========== FILE TYPE METHODS ========== */

    /**
     * Get file extension from media
     */
    public function getFileExtension()
    {
        $media = $this->getFirstMedia('submission_files');
        if (!$media) {
            return null;
        }
        
        return pathinfo($media->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get appropriate Font Awesome icon class for file type
     */
    public function getFileIcon()
    {
        $extension = $this->getFileExtension();
        if (!$extension) {
            return self::FILE_ICONS['default']['icon'];
        }
        
        $extension = strtolower($extension);
        return self::FILE_ICONS[$extension]['icon'] ?? self::FILE_ICONS['default']['icon'];
    }

    /**
     * Get appropriate color class for file type
     */
    public function getFileIconColor()
    {
        $extension = $this->getFileExtension();
        if (!$extension) {
            return self::FILE_ICONS['default']['color'];
        }
        
        $extension = strtolower($extension);
        return self::FILE_ICONS[$extension]['color'] ?? self::FILE_ICONS['default']['color'];
    }

    /**
     * Check if file is an image
     */
    public function isImageFile()
    {
        $extension = $this->getFileExtension();
        if (!$extension) {
            return false;
        }
        
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
        return in_array(strtolower($extension), $imageExtensions);
    }

    /**
     * Check if this submission is in an archived semester
     */
    public function getIsArchivedAttribute()
    {
        return $this->requirement && $this->requirement->semester && !$this->requirement->semester->is_active;
    }

    /* ========== ACCESSORS ========== */

    public static function getPriorityColor($priority)
    {
        return match(strtolower($priority)) {
            'high' => '#dc2626', // red-600
            'medium' => '#ea580c', // orange-600
            'low' => '#16a34a', // green-600
            default => '#6b7280', // gray-500
        };
    }

    public function getStatusTextAttribute()
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function submissionIndicator()
    {
        return $this->hasOne(RequirementSubmissionIndicator::class, 'requirement_id', 'requirement_id')
            ->where('user_id', $this->user_id)
            ->where('course_id', $this->course_id);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_REVISION_NEEDED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
            self::STATUS_UPLOADED => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getStatusColor($status)
    {
        return match($status) {
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_REVISION_NEEDED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
            self::STATUS_UPLOADED => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getIsApprovedAttribute()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsRejectedAttribute()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getNeedsRevisionAttribute()
    {
        return $this->status === self::STATUS_REVISION_NEEDED;
    }

    public function getIsUploadedAttribute()
    {
        return $this->status === self::STATUS_UPLOADED;
    }

    public function getIsUnderReviewAttribute()
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public static function statusesForReview()
    {
        return [
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_REVISION_NEEDED => 'Revision Required',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPROVED => 'Approved',
        ];
    }

    /* ========== BOOT ========== */

    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->deleteFile();
        });

        static::creating(function ($model) {
            $model->submitted_at = now();
            if (empty($model->status)) {
                $model->status = self::STATUS_UPLOADED; // Changed to UPLOADED as default
            }
        });

        static::updated(function ($model) {
            if ($model->isDirty('status')) {
                $oldStatus = $model->getOriginal('status');
                $newStatus = $model->status;

                // Log activity
                if (class_exists('\Spatie\Activitylog\Traits\LogsActivity')) {
                    activity()
                        ->performedOn($model)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                        ])
                        ->log('Status updated');
                }

                // Send notification
                if ($model->relationLoaded('user') && $model->user) {
                    $model->user->notify(
                        new \App\Notifications\SubmissionStatusChanged(
                            $model->requirement->name,
                            $oldStatus,
                            $newStatus,
                            $model->admin_notes,
                            $model->requirement_id
                        )
                    );
                }
            }
        });

        // Automatically move uploaded submissions to under review when they get tracked in indicator
        static::saved(function ($model) {
            if ($model->shouldBeMovedToUnderReview()) {
                $model->moveToUnderReview();
            }
        });
    }
}