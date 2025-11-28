<?php

namespace App\Livewire\User\Dashboard;

use App\Models\Semester;
use Livewire\Component;

class ActiveSemester extends Component
{
    public $currentSemester;
    public $daysRemaining;
    public $semesterProgress;

    protected $listeners = [
        'semesterActivated' => 'refreshSemesterData',
        'semesterArchived' => 'refreshSemesterData'
    ];

    public function mount()
    {
        $this->refreshSemesterData();
    }

    public function refreshSemesterData()
    {
        $this->currentSemester = Semester::getActiveSemester();
        
        if ($this->currentSemester) {
            $this->calculateSemesterStats();
        }
    }

    private function calculateSemesterStats()
    {
        $now = now();
        $startDate = $this->currentSemester->start_date;
        $endDate = $this->currentSemester->end_date;

        // Calculate days remaining
        $this->daysRemaining = $now->diffInDays($endDate, false);
        
        // If semester has ended, set days remaining to 0
        if ($this->daysRemaining < 0) {
            $this->daysRemaining = 0;
        }

        // Calculate semester progress percentage
        $totalDays = $startDate->diffInDays($endDate);
        $daysPassed = $startDate->diffInDays($now);
        
        if ($totalDays > 0) {
            $this->semesterProgress = min(100, max(0, ($daysPassed / $totalDays) * 100));
        } else {
            $this->semesterProgress = 0;
        }
    }

    public function getStatusColorProperty()
    {
        if (!$this->currentSemester) {
            return 'text-gray-500';
        }

        if ($this->daysRemaining <= 0) {
            return 'text-red-500';
        } elseif ($this->daysRemaining <= 30) {
            return 'text-orange-500';
        } else {
            return 'text-green-500';
        }
    }

    public function getProgressColorProperty()
    {
        if ($this->semesterProgress >= 90) {
            return 'bg-red-500';
        } elseif ($this->semesterProgress >= 70) {
            return 'bg-orange-500';
        } else {
            return 'bg-green-500';
        }
    }

    public function render()
    {
        return view('livewire.user.dashboard.active-semester');
    }
}