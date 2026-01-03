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
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
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
     * Get signature URL with fallback
     */
    public function getSignatureUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('signatures');
        
        if (!$media) {
            return null;
        }
        
        // Return a route URL instead of direct storage URL
        return route('admin.signatory.signature.preview', $this->id);
    }

    /**
     * Get signature download URL
     */
    public function getSignatureDownloadUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('signatures');
        
        if (!$media) {
            return null;
        }
        
        return route('admin.signatory.signature.download', $this->id);
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
     * Add signature media with proper handling
     */
    public function addSignature($file)
    {
        // Delete existing signature first
        $this->deleteSignature();
        
        // Add new signature with optimized settings
        return $this->addMedia($file)
            ->usingFileName(md5(time()) . '.' . $file->getClientOriginalExtension())
            ->toMediaCollection('signatures');
    }

    /**
     * Delete signature media
     */
    public function deleteSignature()
    {
        $this->clearMediaCollection('signatures');
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting a signatory, also delete associated media
        static::deleting(function ($signatory) {
            $signatory->deleteSignature();
        });
    }
}