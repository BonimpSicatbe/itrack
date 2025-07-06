<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Requirement;

class RequirementMedia extends Model
{
    protected $fillable = ['requirement_id', 'media_id'];

    public function requirement() {
        return $this->belongsTo(Requirement::class);
    }

    public function media() {
        return $this->belongsTo(Media::class);
    }
}
