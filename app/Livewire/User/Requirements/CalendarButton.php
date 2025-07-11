<?php

namespace App\Livewire\user\Requirements;

use Livewire\Component;
use App\Models\Requirement;
use Illuminate\Support\Facades\Auth;

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
            ->where('assigned_to', Auth::user()->college->name)
            ->orWhere('assigned_to', Auth::user()->department->name)
            ->get()
            ->map(function ($req) {
                $submissionStatus = $req->userSubmissions->first()?->status ?? 'pending';

                // Using string literals instead of constants
                $statusColor = match ($submissionStatus) {
                    'approved' => '#a7c957',
                    'rejected' => '#ba181b',
                    'revision_needed' => '#ffba08',
                    'under_review' => '#84dcc6',
                    default => '#6b7280'
                };

                $finalColor = $req->isOverdue() ? '#ba181b' : $statusColor;

                return [
                    'title' => $req->name,
                    'start' => $req->due->format('Y-m-d'),
                    'color' => $finalColor,
                    'allDay' => true,
                    'extendedProps' => [
                        'description' => $req->description,
                        'priority' => $req->priority,
                        'status' => $submissionStatus,
                        'isOverdue' => $req->isOverdue()
                    ]
                ];
            })->toArray();

        $this->dispatch('requirementsUpdated');
    }

    public function render()
    {
        return view('livewire.user.requirements.calendar-button');
    }
}
