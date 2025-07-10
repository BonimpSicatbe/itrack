<?php

namespace App\Livewire\user\Dashboard;

use Livewire\Component;
use App\Models\Requirement;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Pending extends Component
{
    public function render()
    {
        $user = Auth::user();
        $pendingRequirements = $user->requirements->where('status', 'pending');


        // $pendingRequirements = Requirement::where(function($query) use ($user) {
        //         $query->where(function($q) use ($user) {
        //             $q->where('target', 'college')
        //               ->where('target_id', $user->college_id);
        //         })
        //         ->orWhere(function($q) use ($user) {
        //             $q->where('target', 'department')
        //               ->where('target_id', $user->department_id);
        //         });
        //     })
        //     ->where('status', 'pending')
        //     ->orderBy('due', 'asc')
        //     ->get();

        return view('livewire.user.dashboard.pending', [
            'pendingRequirements' => $pendingRequirements
        ]);
    }
}
