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

class Requirement extends Model implements HasMedia
{
    use InteractsWithMedia, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'due',
        'target',
        'target_id',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'updated_by',
        'archived_by',
    ];

    protected $casts = [
        'due' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Define media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('guides')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile();

        $this->addMediaCollection('submissions')
            ->useDisk(config('media-library.disk_name'));
    }

    // Media conversions for previews (optional)
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

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(SubmittedRequirement::class);
    }

    public function userSubmissions(): HasMany
    {
        return $this->hasMany(SubmittedRequirement::class)
            ->where('user_id', auth()->id())
            ->with(['media', 'reviewer'])
            ->latest();
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function guides(): MorphMany
    {
        return $this->media()->where('collection_name', 'guides');
    }

    // Get assigned users to the requirement
    public function targetUsers()
    {
        if ($this->target === 'college') {
            return User::where('college_id', $this->target_id)->get();
        } elseif ($this->target === 'department') {
            return User::where('department_id', $this->target_id)->get();
        } else {
            return collect();
        }
    }

    // Helper method to check if due date is passed
    public function isOverdue(): bool
    {
        return Carbon::now()->gt($this->due);
    }
}