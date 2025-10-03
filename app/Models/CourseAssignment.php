<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAssignment extends Pivot
{
    /**
     * The name of the custom pivot table.
     * @var string
     */
    protected $table = 'course_assignments';

    /**
     * The primary key for the pivot table.
     * @var string
     */
    protected $primaryKey = 'assignment_id';
    
    /**
     * Indicates if the IDs are auto-incrementing.
     * @var bool
     */
    public $incrementing = true;


    /**
     * Get the specific Course associated with this assignment record.
     */
    public function course(): BelongsTo
    {
        // 'course_id' is the foreign key in the course_assignments table
        return $this->belongsTo(Course::class, 'course_id'); 
    }

    /**
     * Get the specific Professor associated with this assignment record.
     */
    public function professor(): BelongsTo
    {
        // 'professor_id' is the foreign key in the course_assignments table
        return $this->belongsTo(User::class, 'professor_id');
    }
}
