<?php

namespace App\Livewire\User\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubmittedRequirement;

class Progress extends Component
{
    public $statusCounts = [];
    public $totalSubmissions = 0;
    public $statusPercentages = [];
    public $statusPercentagesRaw = []; // For precise calculations

    public function mount()
    {
        $userId = Auth::id();
        
        // Get counts of submissions by status for the current user
        $this->statusCounts = SubmittedRequirement::where('user_id', $userId)
            ->select('status', \DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->totalSubmissions = array_sum($this->statusCounts);
        
        // Calculate percentages with decimals
        if ($this->totalSubmissions > 0) {
            foreach ($this->statusCounts as $status => $count) {
                $percentage = ($count / $this->totalSubmissions) * 100;
                $this->statusPercentagesRaw[$status] = $percentage;
                $this->statusPercentages[$status] = number_format($percentage, 1); // One decimal place
            }
        }
    }

    public function getStatusColor($status)
    {
        return match($status) {
            SubmittedRequirement::STATUS_APPROVED => 'bg-green-500',
            SubmittedRequirement::STATUS_REJECTED => 'bg-red-500',
            SubmittedRequirement::STATUS_REVISION_NEEDED => 'bg-yellow-500',
            SubmittedRequirement::STATUS_UNDER_REVIEW => 'bg-blue-500',
            default => 'bg-gray-500'
        };
    }

    public function getStatusLabel($status)
    {
        return match($status) {
            SubmittedRequirement::STATUS_APPROVED => 'Approved',
            SubmittedRequirement::STATUS_REJECTED => 'Rejected',
            SubmittedRequirement::STATUS_REVISION_NEEDED => 'Revision Needed',
            SubmittedRequirement::STATUS_UNDER_REVIEW => 'Under Review',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    public function render()
    {
        return view('livewire.user.dashboard.progress');
    }
}