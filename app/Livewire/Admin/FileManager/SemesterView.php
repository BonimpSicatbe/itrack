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
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->currentSemester = Semester::getActiveSemester();
        $this->previousSemesters = Semester::where('is_active', false)->orderBy('end_date', 'desc')->get();
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