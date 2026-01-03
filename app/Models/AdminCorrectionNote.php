<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminCorrectionNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'submitted_requirement_id', 
        'requirement_id',
        'course_id', 
        'user_id',
        'admin_id',
        'correction_notes',
        'file_name',
        'status',
        // Removed: 'addressed_at'
    ];

    protected $casts = [
        // Removed: 'addressed_at' => 'datetime',
    ];

    // Status constants - matching SubmittedRequirement statuses
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REVISION_NEEDED = 'revision_needed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPROVED = 'approved';

    /**
     * Get the status options for selection
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_UPLOADED => 'Uploaded',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_REVISION_NEEDED => 'Revision Required',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPROVED => 'Approved',
        ];
    }

    /**
     * Relationship with SubmittedRequirement (NEW)
     */
    public function submittedRequirement(): BelongsTo
    {
        return $this->belongsTo(SubmittedRequirement::class);
    }

    /**
     * Relationship with Requirement
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    /**
     * Relationship with Course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relationship with User (the submitter)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Admin (the one who created the note)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope for rejected notes
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope for approved notes
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for notes needing revision
     */
    public function scopeNeedsRevision($query)
    {
        return $query->where('status', self::STATUS_REVISION_NEEDED);
    }

    /**
     * Scope for notes under review
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    /**
     * Scope for uploaded notes
     */
    public function scopeUploaded($query)
    {
        return $query->where('status', self::STATUS_UPLOADED);
    }

    /**
     * Scope for notes by submitted requirement
     */
    public function scopeBySubmission($query, $submittedRequirementId)
    {
        return $query->where('submitted_requirement_id', $submittedRequirementId);
    }

    /**
     * Scope for notes by requirement and course (keep for backward compatibility)
     */
    public function scopeByRequirementAndCourse($query, $requirementId, $courseId, $userId = null)
    {
        $query->where('requirement_id', $requirementId)
              ->where('course_id', $courseId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query;
    }

    /**
     * Check if note is for rejected file
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if note is for approved file
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if note needs revision
     */
    public function needsRevision(): bool
    {
        return $this->status === self::STATUS_REVISION_NEEDED;
    }

    /**
     * Check if note is under review
     */
    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    /**
     * Check if note is uploaded
     */
    public function isUploaded(): bool
    {
        return $this->status === self::STATUS_UPLOADED;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeAttribute(): string
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

    /**
     * Get status text for display
     */
    public function getStatusTextAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    /**
     * Get the current file name from the submission (NEW helper method)
     */
    public function getCurrentFileName(): ?string
    {
        if ($this->submittedRequirement && $this->submittedRequirement->submissionFile) {
            return $this->submittedRequirement->submissionFile->file_name;
        }
        
        return $this->file_name; // Fallback to original file name
    }

    /**
     * Check if the file has been replaced since note was created (NEW)
     */
    public function hasFileBeenReplaced(): bool
    {
        if (!$this->submittedRequirement || !$this->submittedRequirement->submissionFile) {
            return false;
        }

        return $this->file_name !== $this->submittedRequirement->submissionFile->file_name;
    }
}