<?php

namespace App\Livewire\Admin\FileManager;

use Livewire\Component;
use App\Models\Semester;
use Illuminate\Support\Carbon;

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
        
        $today = Carbon::today();
        
        // Get current semester: either active OR the one that spans today's date
        $this->currentSemester = Semester::where('is_active', true)
            ->orWhere(function($query) use ($today) {
                $query->where('start_date', '<=', $today)
                      ->where('end_date', '>=', $today);
            })
            ->orderBy('is_active', 'desc') // Prioritize manually activated semesters
            ->first();
            
        // Get previous semesters: only semesters that have ended (end_date < today)
        // This excludes future semesters that haven't started yet
        $this->previousSemesters = Semester::where('end_date', '<', $today)
            ->where('id', '!=', $this->currentSemester?->id)
            ->orderBy('end_date', 'desc')
            ->get();
            
        \Log::debug('Refreshed data:', [
            'current' => $this->currentSemester?->id,
            'previous_count' => $this->previousSemesters->count(),
            'today' => $today->format('Y-m-d')
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