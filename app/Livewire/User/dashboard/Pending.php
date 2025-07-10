<?php

namespace App\Livewire\user\Dashboard;

use Livewire\Component;
use App\Models\Requirement;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
{
    public function render()
    {
        $user = Auth::user();

        $requirements = Requirement::where('assigned_to', $user->college->name)
            ->orWhere('assigned_to', $user->department->name)
            ->where('status', 'pending')
            ->orderBy('due', 'asc')
            ->get();

        return view('livewire.user.dashboard.pending', [
            'pendingRequirements' => $requirements,
        ]);
    }
}
