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

    public static function getStatusColor($status)
    {
        $colors = [
            self::STATUS_APPROVED => '#a7c957', // Green (success)
            self::STATUS_REJECTED => '#ba181b', // Red (error) 
            self::STATUS_REVISION_NEEDED => '#ffba08', // Amber (warning)
            self::STATUS_UNDER_REVIEW => '#84dcc6', // Blue (info)
            'default' => '#6b7280',                // Gray (neutral)
        ];

        return $colors[$status] ?? $colors['default'];
    }

    public static function getPriorityColor($priority)
    {
        $colors = [
            'high' => '#ef4444',    // Red
            'medium' => '#f59e0b',  // Amber
            'low' => '#3b82f6',     // Blue
            'default' => '#023e8a', // Gray
        ];

        return $colors[$priority] ?? $colors['default'];
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