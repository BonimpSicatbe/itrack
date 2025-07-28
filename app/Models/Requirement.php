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
    ];

    protected $casts = [
        'due' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('guides')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile();

        $this->addMediaCollection('submissions')
            ->useDisk(config('media-library.disk_name'));
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

    // ========== Relationships ==========
    public function users(): BelongsTo
    {
        return $this->belongsTo(
            User::class, 'college_id', 'department_id',
            $this->assigned_to, 'name');
    }

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
            ->where('user_id', Auth::id())
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

    // ========== Methods from Incoming ==========
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

        // Always return a collection to avoid errors
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
}
