<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('submission_files')
            ->singleFile();
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
            self::STATUS_UNDER_REVIEW => 'badge-accent',
            default => 'badge-neutral', // For Pending status
        };
    }

    // Automatically set submitted_at when creating a new submission
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->submitted_at = now();
            if (empty($model->status)) {
                $model->status = self::STATUS_UNDER_REVIEW;
            }
        });
    }
}