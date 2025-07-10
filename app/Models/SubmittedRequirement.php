<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

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

    public static function statuses()
    {
        return [
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_REVISION_NEEDED => 'Revision Needed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPROVED => 'Approved',
        ];
    }

    public static function getStatusColor($status)
    {
        $colors = [
            self::STATUS_APPROVED => '#a7c957',
            self::STATUS_REVISION_NEEDED => '#ffba08',
            self::STATUS_UNDER_REVIEW => '#84dcc6',
            'default' => '#6b7280',
        ];

        return $colors[$status] ?? $colors['default'];
    }

    public static function getPriorityColor($priority)
    {
        $colors = [
            'high' => '#ef4444',
            'medium' => '#f59e0b',
            'low' => '#3b82f6',
            'default' => '#023e8a',
        ];

        return $colors[$priority] ?? $colors['default'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('submission_files')
            ->singleFile()
            ->useDisk('public');
    }

    public function submissionFile(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', 'submission_files');
    }

    public function getSubmissionFileAttribute()
    {
        return $this->submissionFile()->first();
    }

    public function addSubmissionFile($file)
    {
        return $this->addMedia($file)->toMediaCollection('submission_files');
    }

    public function getFileUrl()
    {
        if (!$this->submissionFile) {
            return null;
        }
        return Storage::disk('public')->url($this->submissionFile->getPathRelativeToRoot());
    }

    public function getFilePath()
    {
        if (!$this->submissionFile) {
            return null;
        }
        return Storage::disk('public')->path($this->submissionFile->getPathRelativeToRoot());
    }

    /**
     * Delete the associated file and its record
     */
    public function deleteFile()
    {
        if ($this->submissionFile) {
            $this->submissionFile->delete();
            return true;
        }
        return false;
    }

    /**
     * Check if the submission can be deleted by the current user
     */
    public function canBeDeletedBy($user)
    {
        return $user->id === $this->user_id && 
               $this->status !== self::STATUS_APPROVED;
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusTextAttribute()
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-error',
            self::STATUS_REVISION_NEEDED => 'badge-warning',
            default => 'badge-info',
        };
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            // Delete associated file when the submission is deleted
            $model->deleteFile();
        });

        static::creating(function ($model) {
            $model->submitted_at = now();
            if (empty($model->status)) {
                $model->status = self::STATUS_UNDER_REVIEW;
            }
        });
    }
}