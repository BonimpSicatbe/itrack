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
            'high' => '#f87171',    // red
            'medium' => '#fbbf24',  // amber
            'low' => '#a3e635',    // lime
            'default' => '#9ca3af', // gray
        ];

        return $colors[strtolower($priority)] ?? $colors['default'];
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

    public function deleteFile()
    {
        if ($this->submissionFile) {
            $this->submissionFile->delete();
            return true;
        }
        return false;
    }

    public function canBeDeletedBy($user)
    {
        return $user->id === $this->user_id &&
               $this->status !== self::STATUS_APPROVED;
    }

    /* ========== ACCESSORS ========== */

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