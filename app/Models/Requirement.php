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

    // Define media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('guides')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile(); // If you want only one guide file per requirement

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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
