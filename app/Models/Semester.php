<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public static function getActiveSemester()
    {
        return self::latest()->first();
    }

    public static function getArchivedSemester()
    {
        return self::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->first();
    }

    public static function getAllArchivedSemesters()
    {
        return self::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->get();
    }

    public static function getPreviousSemester()
    {
        return self::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeDuring($query, $date)
    {
        return $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
    }

    public function setActive()
    {
        // Deactivate all semesters first
        self::query()->update(['is_active' => false]);

        // Activate this semester
        $this->update(['is_active' => true]);

        return $this;
    }

    // NEW: Check if semester should be auto-archived
    public function shouldAutoArchive()
    {
        return $this->is_active && now()->greaterThan($this->end_date);
    }

    // NEW: Auto-archive this semester
    public function autoArchive()
    {
        if ($this->shouldAutoArchive()) {
            $this->update(['is_active' => false]);
            Log::info("Semester auto-archived: {$this->name}");
            return true;
        }
        return false;
    }

    // REMOVED: archiveActiveSemester method since it was trying to use non-existent fields

    // Add this relationship to Semester.php
    public function requirements()
    {
        return $this->hasMany(\App\Models\Requirement::class, 'semester_id', 'id');
    }

    public function submittedRequirements()
    {
        // lets you jump Semester -> SubmittedRequirement (through Requirement)
        return $this->hasManyThrough(
            \App\Models\SubmittedRequirement::class,  // final model
            \App\Models\Requirement::class,           // through model
            'semester_id',    // FK on requirements → semesters.id
            'requirement_id', // FK on submitted_requirements → requirements.id
            'id',             // local key on semesters
            'id'              // local key on requirements
        );
    }

    // ADD THIS: Relationship to course assignments
    public function courseAssignments()
    {
        return $this->hasMany(\App\Models\CourseAssignment::class, 'semester_id', 'id');
    }

    // ADD THIS: Get courses assigned to a specific professor in this semester
    public function getCoursesForProfessor($professorId)
    {
        return $this->courseAssignments()
            ->where('professor_id', $professorId)
            ->with('course')
            ->get()
            ->pluck('course')
            ->unique('id')
            ->values();
    }

    // ADD THIS: Count courses assigned to a specific professor in this semester
    public function getCourseCountForProfessor($professorId)
    {
        return $this->courseAssignments()
            ->where('professor_id', $professorId)
            ->count();
    }

    // ADD THIS: Check if professor has any courses in this semester
    public function hasCoursesForProfessor($professorId)
    {
        return $this->courseAssignments()
            ->where('professor_id', $professorId)
            ->exists();
    }
}