<?php

namespace App\Livewire\user\PendingTask;

use Livewire\Component;
use App\Models\Requirement;

class CalendarButton extends Component
{
    public $requirements;

    protected $listeners = ['refreshCalendar' => 'loadRequirements'];

    public function mount()
    {
        $this->loadRequirements();
    }

    public function loadRequirements()
    {
        $this->requirements = Requirement::query()
            ->where('target_id', auth()->id())
            ->whereHas('userSubmissions', fn($q) => $q->where('status', '!=', 'approved'))
            ->get()
            ->map(fn($req) => [
                'title' => $req->name,
                'start' => $req->due->format('Y-m-d'),
                'color' => $req->isOverdue() ? '#ef4444' : '#3b82f6',
                'extendedProps' => [
                    'description' => $req->description,
                    'priority' => $req->priority,
                ]
            ])->toArray();

        $this->dispatch('requirementsUpdated');
    }

    public function render()
    {
        return view('livewire.user.pending-task.calendar-button');
    }
}