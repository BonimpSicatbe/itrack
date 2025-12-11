<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Signatory extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'position',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signatures')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);
    }

    /**
     * Get the signature media
     */
    public function signature(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', 'signatures');
    }

    /**
     * Get signature URL
     */
    public function getSignatureUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('signatures');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get signature path
     */
    public function getSignaturePathAttribute(): ?string
    {
        $media = $this->getFirstMedia('signatures');
        return $media ? $media->getPath() : null;
    }

    /**
     * Check if signatory has signature
     */
    public function getHasSignatureAttribute(): bool
    {
        return $this->getFirstMedia('signatures') !== null;
    }

    /**
     * Add signature media
     */
    public function addSignature($file)
    {
        return $this->addMedia($file)->toMediaCollection('signatures');
    }

    /**
     * Delete signature media
     */
    public function deleteSignature()
    {
        $this->clearMediaCollection('signatures');
    }
}