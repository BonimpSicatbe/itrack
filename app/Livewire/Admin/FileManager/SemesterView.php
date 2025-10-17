<?php

namespace App\Livewire\Admin\FileManager;

use Livewire\Component;
use App\Models\Semester;

class SemesterView extends Component
{
    public $currentSemester;
    public $previousSemesters;
    public $showSemesterPanel = true;

    protected $listeners = [
        'semesterActivated' => 'refreshData',
        'hide-notification-after-delay' => 'hideNotification'
    ];

    public function mount()
    {
        \Log::debug('Active semester check:', [
            'getActiveSemester' => Semester::getActiveSemester(),
            'all_semesters' => Semester::all()->toArray()
        ]);
        
        $this->refreshData();
    }

    public function refreshData()
    {
        // Clear any potential caching
        $this->currentSemester = null;
        $this->previousSemesters = collect();
        
        // Fresh database queries
        $this->currentSemester = Semester::where('is_active', true)->first();
        $this->previousSemesters = Semester::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->get();
            
        \Log::debug('Refreshed data:', [
            'current' => $this->currentSemester?->id,
            'previous_count' => $this->previousSemesters->count()
        ]);
    }

    public function showSemesterFiles($semesterId)
    {
        $this->dispatch('semesterSelected', semesterId: $semesterId);
    }
    
    public function hideNotification()
    {
        // This method handles the hide event if needed
    }

    public function render()
    {
        return view('livewire.admin.file-manager.semester-view');
    }
}