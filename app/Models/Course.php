<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'courses';

    /**
     * The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'course_code',
        'course_name',
        'description',
    ];


    /**
     * Get all the individual assignment records for this course (historical).
     * This links directly to the pivot table.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class, 'course_id');
    }

    /**
     * Get all the professors who have ever taught this course (current and historical).
     * This uses the many-to-many relationship.
     */
    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_assignments', 'course_id', 'professor_id')
                    ->using(CourseAssignment::class) // Specifies the custom pivot model
                    // Include the extra fields from the pivot table
                    ->withPivot('assignment_id', 'year', 'semester', 'assignment_date')
                    ->withTimestamps();
    }
}
