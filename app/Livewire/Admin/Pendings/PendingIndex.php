<?php

namespace App\Livewire\Admin\Pendings;

use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PendingIndex extends Component
{
    public function mount()
    {

    }

    public function render()
    {
        $user = Auth::user();

        $requirements = Requirement::where('status', 'pending')
            ->orWhere('assigned_to', $user->college->name)
            ->orWhere('assigned_to', $user->department->name)
            ->orderBy('due', 'desc')
            ->get();

        return view('livewire.admin.pendings.pending-index', [
            'pendings' => $requirements,
        ]);
    }
}
