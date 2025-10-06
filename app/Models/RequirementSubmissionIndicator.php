<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementSubmissionIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_id',
        'user_id',
        'course_id', 
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * The requirement this submission indicator belongs to
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    /**
     * The user who submitted this requirement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The course this submission indicator belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}