<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use App\Models\Requirement;
use Livewire\WithPagination;

class Pending extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'due';
    public $sortDirection = 'desc';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Requirement::where('status', 'pending');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.admin.dashboard.pending', [
            'pendings' => $query->paginate(20),
        ]);
    }
}
