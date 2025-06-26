<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Submission extends Model
{
    protected $fillable = [
        'requirement_id',
        'user_id',
        'media_id',
    ];

    // ========== ========== RELATIONSHIPS | START ========== ==========
    public function requirement()
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
    // ========== ========== RELATIONSHIPS | START ========== ==========

    // ========== ========== ----- | START ========== ==========
    /**
     *
     * 1. get users who have submitted a requirement
     * 2. get users who have not submitted a requirement
     * 3.
     *
     **/
    // ========== ========== ----- | END ========== ==========
}
