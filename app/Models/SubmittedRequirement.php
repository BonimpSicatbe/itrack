<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'submitted_at',
        'signed_document_path', // Added
        'signed_at', // Added
        'signatory_id', // Added
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'signed_at' => 'datetime', // Added
    ];

    // Status Constants
    const STATUS_UPLOADED = 'uploaded'; 
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
        'xls' => ['icon' => 'fa-file-excel', 'color' => 'text-green-600'],
        'xlsx' => ['icon' => 'fa-file-excel', 'color' => 'text-green-600'],
        'ppt' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-500'],
        'pptx' => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-500'],
        'jpg' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'jpeg' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'png' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
        'gif' => ['icon' => 'fa-file-image', 'color' => 'text-purple-500'],
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
     * The signatory who signed this document
     */
    public function signatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class);
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
     * Media relationship for signed documents
     */
    public function signedDocument(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', 'signed_documents');
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

    /**
     * Relationship with Admin Correction Notes
     */
    public function correctionNotes(): HasMany
    {
        return $this->hasMany(AdminCorrectionNote::class, 'submitted_requirement_id');
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
     * Scope for signed documents
     */
    public function scopeSigned($query)
    {
        return $query->whereNotNull('signed_document_path')
                    ->whereNotNull('signed_at');
    }

    /**
     * Scope for approved submissions with signatures
     */
    public function scopeApprovedWithSignature($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
                    ->whereNotNull('signed_document_path');
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

        // Add collection for signed documents
        $this->addMediaCollection('signed_documents')
            ->singleFile()
            ->useDisk('public');
    }

    public function getSubmissionFileAttribute()
    {
        return $this->submissionFile()->first();
    }

    public function getSignedDocumentAttribute()
    {
        return $this->signedDocument()->first();
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

    /**
     * Add a signed document to the submission
     */
    public function addSignedDocument($filePath, $signatoryId = null)
    {
        $media = $this->addMedia($filePath)->toMediaCollection('signed_documents');
        
        $this->update([
            'signed_document_path' => $media->getPath(),
            'signed_at' => now(),
            'signatory_id' => $signatoryId,
        ]);
        
        return $media;
    }

    /**
     * Remove signed document from submission
     */
    public function removeSignedDocument()
    {
        $this->clearMediaCollection('signed_documents');
        
        $this->update([
            'signed_document_path' => null,
            'signed_at' => null,
            'signatory_id' => null,
        ]);
    }

    public function getFileUrl()
    {
        // If approved and has signed document, return signed version
        if ($this->isApproved && $this->hasSignedDocument()) {
            return $this->getSignedDocumentUrl();
        }
        
        // Otherwise, return original file
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        return $media->getUrl();
    }

    public function getOriginalFileUrl()
    {
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        return $media->getUrl();
    }

    public function getSignedDocumentUrl()
    {
        $media = $this->getFirstMedia('signed_documents');
        
        if (!$media) {
            return null;
        }
        
        return $media->getUrl();
    }

    /**
     * Get the appropriate URL for preview (signed version if available)
     */
    public function getPreviewUrl()
    {
        // Always show signed version if it exists and submission is approved
        if ($this->isApproved && $this->has_signed_document) {
            return $this->getSignedDocumentUrl();
        }
        
        // Otherwise, show original file
        return $this->getOriginalFileUrl();
    }

    /**
     * Get the preview URL for route generation
     * This returns the route for file preview
     */
    public function getPreviewRouteUrl()
    {
        // Always show signed version if it exists and submission is approved
        if ($this->isApproved && $this->has_signed_document) {
            $media = $this->getFirstMedia('signed_documents');
            if ($media) {
                return route('file.preview.signed', ['submission' => $this->id]);
            }
        }
        
        // Otherwise, show original file
        $media = $this->getFirstMedia('submission_files');
        if ($media) {
            return route('file.preview', ['submission' => $this->id]);
        }
        
        return null;
    }

    public function getFilePath()
    {
        // If approved and has signed document, return signed version path
        if ($this->isApproved && $this->hasSignedDocument()) {
            return $this->getSignedDocumentPath();
        }
        
        // Otherwise, return original file path
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        return $media->getPath();
    }

    public function getOriginalFilePath()
    {
        $media = $this->getFirstMedia('submission_files');
        
        if (!$media) {
            return null;
        }
        
        return $media->getPath();
    }

    public function getSignedDocumentPath()
    {
        $media = $this->getFirstMedia('signed_documents');
        
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

    /**
     * Delete signed document
     */
    public function deleteSignedDocument()
    {
        $media = $this->getFirstMedia('signed_documents');
        if ($media) {
            $media->delete();
            $this->removeSignedDocument();
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
        // If approved and has signed document, return PDF extension
        if ($this->isApproved && $this->hasSignedDocument()) {
            return 'pdf';
        }
        
        $media = $this->getFirstMedia('submission_files');
        if (!$media) {
            return null;
        }
        
        return pathinfo($media->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get original file extension
     */
    public function getOriginalFileExtension()
    {
        $media = $this->getFirstMedia('submission_files');
        if (!$media) {
            return null;
        }
        
        return pathinfo($media->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get signed document extension (always PDF)
     */
    public function getSignedDocumentExtension()
    {
        if ($this->has_signed_document) {
            return 'pdf';
        }
        return null;
    }

    /**
     * Get appropriate Font Awesome icon class for file type
     */
    public function getFileIcon()
    {
        // If approved and has signed document, return PDF icon
        if ($this->isApproved && $this->hasSignedDocument()) {
            return 'fa-file-signature';
        }
        
        $extension = $this->getOriginalFileExtension();
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
        // If approved and has signed document, return signature color
        if ($this->isApproved && $this->hasSignedDocument()) {
            return 'text-yellow-600';
        }
        
        $extension = $this->getOriginalFileExtension();
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
        $extension = $this->getOriginalFileExtension();
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

    public function getHasSignedDocumentAttribute()
    {
        return !empty($this->signed_document_path) && $this->getFirstMedia('signed_documents');
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

    /**
     * Get pending correction notes for this submission
     */
    public function pendingCorrectionNotes(): HasMany
    {
        return $this->correctionNotes()->pending();
    }

    /**
     * Check if submission has pending correction notes
     */
    public function hasPendingCorrections(): bool
    {
        return $this->pendingCorrectionNotes()->exists();
    }

    /* ========== HELPER METHODS ========== */

    /**
     * Get the appropriate file name based on status
     */
    public function getDisplayFileName()
    {
        if ($this->isApproved && $this->has_signed_document) {
            $media = $this->getFirstMedia('submission_files');
            $originalName = $media ? $media->name : 'Document';
            return 'SIGNED - ' . $originalName;
        }
        
        $media = $this->getFirstMedia('submission_files');
        return $media ? $media->name : 'No file';
    }

    /**
     * Get file size for display
     */
    public function getFileSize()
    {
        if ($this->isApproved && $this->has_signed_document) {
            $media = $this->getFirstMedia('signed_documents');
        } else {
            $media = $this->getFirstMedia('submission_files');
        }
        
        if (!$media) {
            return '0 bytes';
        }
        
        $bytes = $media->size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Get the original file name (without SIGNED prefix)
     */
    public function getOriginalFileName()
    {
        $media = $this->getFirstMedia('submission_files');
        return $media ? $media->name : 'No file';
    }

    /**
     * Get the signed file name
     */
    public function getSignedFileName()
    {
        $media = $this->getFirstMedia('signed_documents');
        return $media ? $media->name : null;
    }

    /* ========== BOOT ========== */

    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->deleteFile();
            $model->deleteSignedDocument();
        });

        static::creating(function ($model) {
            $model->submitted_at = now();
            if (empty($model->status)) {
                $model->status = self::STATUS_UPLOADED;
            }
        });

        static::updated(function ($model) {
            if ($model->isDirty('status')) {
                $oldStatus = $model->getOriginal('status');
                $newStatus = $model->status;

                // Remove signed document if status changes from approved
                if ($oldStatus === self::STATUS_APPROVED && $newStatus !== self::STATUS_APPROVED) {
                    $model->removeSignedDocument();
                }

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