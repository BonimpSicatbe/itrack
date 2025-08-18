<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Requirement;
use App\Models\User;
use App\Models\Semester;
use Livewire\Component;

class Overview extends Component
{
    public $stats = [];
    public $totalRequirements = 0;

    public function mount()
    {
        $currentSemester = Semester::getActiveSemester();
        $semesterDescription = $currentSemester 
            ? $currentSemester->start_date->format('M d, Y') . ' - ' . $currentSemester->end_date->format('M d, Y')
            : 'No active semester';

        // Base query that will be used for all requirement counts
        $requirementsQuery = Requirement::query();
        
        if ($currentSemester) {
            $requirementsQuery->where('semester_id', $currentSemester->id);
        }

        $this->totalRequirements = $requirementsQuery->count();

        $this->stats = [
            [
                'title' => 'Current Semester',
                'count' => $currentSemester ? $currentSemester->name : 'None',
                'description' => $semesterDescription,
                'icon' => 'fa-calendar-days',
                'color' => 'primary',
            ],
            [
                'title' => 'Total Requirements',
                'count' => $this->totalRequirements,
                'icon' => 'fa-list-check',
                'color' => 'primary',
            ],
            [
                'title' => 'Pending Requirements',
                'count' => $currentSemester 
                    ? Requirement::where('semester_id', $currentSemester->id)
                        ->where('status', 'pending')
                        ->count()
                    : 0,
                'icon' => 'fa-clock',
                'color' => 'warning',
            ],
            [
                'title' => 'Completed',
                'count' => $currentSemester 
                    ? Requirement::where('semester_id', $currentSemester->id)
                        ->where('status', 'completed')
                        ->count()
                    : 0,
                'icon' => 'fa-circle-check',
                'color' => 'success',
            ],
            [
                'title' => 'Due This Week',
                'count' => $currentSemester 
                    ? Requirement::where('semester_id', $currentSemester->id)
                        ->whereBetween('due', [now(), now()->addWeek()])
                        ->where('status', '!=', 'completed')
                        ->count()
                    : 0,
                'icon' => 'fa-calendar-week',
                'color' => 'accent',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.overview');
    }
}