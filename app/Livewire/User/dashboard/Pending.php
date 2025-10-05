<?php

namespace App\Livewire\user\Dashboard;

use Livewire\Component;
use App\Models\Requirement;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CourseAssignment;

class Pending extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Get all pending requirements
        $allPendingRequirements = Requirement::where('status', 'pending')
            ->orderBy('due', 'asc')
            ->get();
        
        // Filter requirements that are assigned to the user based on college/department
        $assignedRequirements = $allPendingRequirements->filter(function($requirement) use ($user) {
            return $requirement->isAssignedToUser($user);
        });

        // Get courses assigned to this user (professor)
        $userCourseAssignments = CourseAssignment::where('professor_id', $user->id)
            ->with('course')
            ->get();

        // Build the pending list: for each requirement, show it for each course the user is assigned to
        $pendingRequirements = collect();
        
        foreach ($assignedRequirements as $requirement) {
            foreach ($userCourseAssignments as $assignment) {
                // Add the requirement for each assigned course
                $pendingRequirements->push([
                    'requirement' => $requirement,
                    'course' => $assignment->course,
                    'assignment' => $assignment, // Include assignment info if needed
                ]);
            }
        }

        return view('livewire.user.dashboard.pending', [
            'pendingRequirements' => $pendingRequirements
        ]);
    }
}