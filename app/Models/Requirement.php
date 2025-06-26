<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    // ========== ========== RELATIONSHIPS | START ========== ==========
    // get the user who created the requirement
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========== ========== RELATIONSHIPS | END ========== ==========

    public function assignedTo()
    {
        return Requirement::where('target', $this->target)
            ->where('target_id', $this->target_id);
    }

    public function assignedTargets()
    {
        if ($this->target === 'college') {
            return User::where('college_id', $this->target_id)->count();
        } elseif ($this->target === 'department') {
            return User::where('department_id', $this->target_id)->count();
        }
    }

    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'info',
            'normal' => 'warning',
            'high' => 'error',
        ][$this->priority] ?? 'neutral';
    }

    // return the assigned college or department based on the target
    public function assignedToType()
    {
        if ($this->target === 'college') {
            return College::find($this->target_id);
        } elseif ($this->target === 'department') {
            return Department::find($this->target_id);
        } else {
            return null; // Return null if target is not recognized
        }
    }

    // get assigned users to the requirement
    public function targetUsers()
    {
        if ($this->target === 'college') {
            return User::where('college_id', $this->target_id)->with('users');
        } elseif ($this->target === 'department') {
            return User::where('department_id', $this->target_id)->with('users');
        } else {
            return collect(); // Return an empty collection if target is not recognized
        }
    }
}
