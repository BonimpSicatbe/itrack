<?php

namespace App\Livewire\user\Dashboard;

use Livewire\Component;
use App\Models\Requirement;

class Pending extends Component
{
    public function render()
    {
        return view('livewire.user.dashboard.pending', [
            'pendingRequirements' => Requirement::where('target_id', auth()->id())
                                             ->where('status', 'pending')
                                             ->orderBy('due', 'asc')
                                             ->get()
        ]);
    }
}
