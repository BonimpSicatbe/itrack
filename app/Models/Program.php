<?php
// app/Models/Program.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_code',
        'program_name',
        'description',
        'college_id'
    ];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}