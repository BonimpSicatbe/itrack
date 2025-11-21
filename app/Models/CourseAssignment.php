<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAssignment extends Model
{
    use HasFactory;
    
    // Assuming your custom primary key is assignment_id
    protected $primaryKey = 'assignment_id';

    // Assuming you mass assign these fields
    protected $fillable = [
        'course_id',
        'professor_id',
        'semester_id', // Add the new semester_id field
        'assignment_date',
    ];

    /**
     * Get the semester associated with the assignment.
     */
    public function semester(): BelongsTo
    {
        // Links the foreign key 'semester_id' on this model to the primary key 'id' on the 'Semester' model
        return $this->belongsTo(Semester::class);
    }
    
    // Assuming you have these other relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function professor(): BelongsTo
    {
        // Assuming 'professor_id' links to the 'User' model
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function getIdAttribute()
    {
        return $this->assignment_id;
    }
}