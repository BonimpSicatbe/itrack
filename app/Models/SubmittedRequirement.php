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

    public function scopeForSemester($query, Semester $semester)
    {
        return $query->whereBetween('created_at', [
            $semester->start_date,
            $semester->end_date
        ]);
    }

    public function getSemesterAttribute()
    {
        return Semester::where('start_date', '<=', $this->created_at)
            ->where('end_date', '>=', $this->created_at)
            ->first();
    }

    /* ========== METHODS ========== */

    public static function statuses()
    {
        return [
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_REVISION_NEEDED => 'Revision Needed',
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
        return $this->addMedia($file)->toMediaCollection('submission_files');
    }

    // FIXED: Updated getFileUrl method
    public function getFileUrl()
    {
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        // Use the media's getUrl() method which handles the URL generation correctly
        return $media->getUrl();
    }

    // FIXED: Updated getFilePath method
    public function getFilePath()
    {
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        // Use the media's getPath() method
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
        return $user->id === $this->user_id &&
               $this->status !== self::STATUS_APPROVED;
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

    /* ========== ACCESSORS ========== */

    public function getStatusTextAttribute()
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_REVISION_NEEDED => 'bg-yellow-100 text-yellow-800',
            default => 'bg-blue-100 text-blue-800', // For under_review and any other status
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

    /* ========== BOOT ========== */

    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->deleteFile();
        });

        static::creating(function ($model) {
            $model->submitted_at = now();
            if (empty($model->status)) {
                $model->status = self::STATUS_UNDER_REVIEW;
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

                // Send notification (remove the auto-approve logic)
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
    }
}