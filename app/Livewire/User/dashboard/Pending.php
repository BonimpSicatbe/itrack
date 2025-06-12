<?php

namespace App\Livewire\User\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Pending extends Component
{
    public $pendings = [];

    public function mount()
    {
    }

    public function render()
    {
        return view('livewire.user.dashboard.pending');
    }
}
