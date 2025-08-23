<?php

namespace App\Livewire\Admin\FileManager;

use Livewire\Component;
use App\Models\Semester;

class SemesterView extends Component
{
    public $currentSemester;
    public $archivedSemester;
    public $archivedSemesters;
    public $showSemesterPanel = true;
    
    // Modal control properties
    public $showArchiveModal = false;
    public $semesterToArchive;

    protected $listeners = [
        'semesterActivated' => 'refreshData',
        'hide-notification-after-delay' => 'hideNotification'
    ];

    public function mount()
    {
        $this->refreshData();
        $this->checkAutoArchiveStatus();
    }

    public function refreshData()
    {
        $this->currentSemester = Semester::getActiveSemester();
        $this->archivedSemester = Semester::getArchivedSemester();
        $this->archivedSemesters = Semester::getAllArchivedSemesters();
    }

    public function showSemesterFiles($semesterId)
    {
        $this->dispatch('semesterSelected', semesterId: $semesterId);
    }

    public function confirmArchive($semesterId)
    {
        $this->semesterToArchive = $semesterId;
        $this->showArchiveModal = true;
    }

    public function archiveSemester()
    {
        $semester = Semester::find($this->semesterToArchive);
        
        if ($semester && $semester->is_active) {
            try {
                // Deactivate the semester
                $semester->update(['is_active' => false]);
                
                // Refresh the data
                $this->refreshData();
                
                // Emit events
                $this->dispatch('semesterArchived');
                $this->dispatch('semesterActivated');
                $this->dispatch('clearSelectedSemester');
                
                // Show success notification
                $this->dispatch('showNotification', 
                    type: 'success', 
                    content: 'Semester archived successfully!',
                    duration: 3000
                );
                
                // Close modal
                $this->closeModal();
                
                // Log manual archiving
                \Log::info("Semester manually archived: {$semester->name} by user");
            } catch (\Exception $e) {
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'Failed to archive semester: ' . $e->getMessage(),
                    duration: 5000
                );
            }
        } else {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Semester not found or already archived',
                duration: 5000
            );
        }
    }
    
    public function closeModal()
    {
        $this->reset(['showArchiveModal', 'semesterToArchive']);
    }
    
    public function hideNotification()
    {
        // This method handles the hide event if needed
    }

    public function checkAutoArchiveStatus()
    {
        $semesters = Semester::where('is_active', true)->get();
        foreach ($semesters as $semester) {
            if ($semester->shouldAutoArchive()) {
                $this->dispatch('showNotification', 
                    type: 'info', 
                    content: "Semester '{$semester->name}' has ended and will be auto-archived soon.",
                    duration: 5000
                );
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.file-manager.semester-view');
    }
}