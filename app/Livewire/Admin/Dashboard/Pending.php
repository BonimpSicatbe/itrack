<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use App\Models\Requirement;
use Livewire\WithPagination;

class Pending extends Component
{
    use WithPagination;

    // ========== ========== SEARCH AND SORT | START ========== ==========
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'asc';
    // protected $queryString = ['sortField', 'sortDirection'];

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';

        $this->sortField = $field;
    }
    // ========== ========== SEARCH AND SORT | END ========== ==========

    public function render()
    {
        return view('livewire.admin.dashboard.pending', [
            'pendings' => Requirement::search('name', $this->search)->orderBy($this->sortField, $this->sortDirection)->paginate(20),
        ]);
    }
}
