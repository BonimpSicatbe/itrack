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
        
        // Calculate percentages
        if ($this->totalSubmissions > 0) {
            foreach ($this->statusCounts as $status => $count) {
                $this->statusPercentages[$status] = number_format(($count / $this->totalSubmissions) * 100, 1);
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
            SubmittedRequirement::STATUS_UPLOADED => 'bg-purple-500', // Added uploaded status color
            default => 'bg-gray-300'
        };
    }

    public function getStatusLabel($status)
    {
        return match($status) {
            SubmittedRequirement::STATUS_APPROVED => 'Approved',
            SubmittedRequirement::STATUS_REJECTED => 'Rejected',
            SubmittedRequirement::STATUS_REVISION_NEEDED => 'Revision Required',
            SubmittedRequirement::STATUS_UNDER_REVIEW => 'Under Review',
            SubmittedRequirement::STATUS_UPLOADED => 'Uploaded', // Added uploaded status label
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    public function render()
    {
        return view('livewire.user.dashboard.progress');
    }
}