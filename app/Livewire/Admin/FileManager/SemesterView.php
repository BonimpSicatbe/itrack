<?php

namespace App\Livewire\Admin\FileManager;

use Livewire\Component;
use App\Models\Semester;

class SemesterView extends Component
{
    public $currentSemester;
    public $previousSemester;

    protected $listeners = ['semesterActivated' => 'refreshData'];

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->currentSemester = Semester::getActiveSemester();
        $this->previousSemester = Semester::getPreviousSemester();
    }

    public function activateSemester($semesterId)
    {
        $semester = Semester::findOrFail($semesterId);
        $semester->setActive();
        
        $this->refreshData();
        $this->dispatch('semesterActivated');
    }

    public function render()
    {
        return view('livewire.admin.file-manager.semester-view');
    }
}