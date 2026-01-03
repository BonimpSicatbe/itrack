<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Signatory;
use Illuminate\Support\Facades\Storage;

class SignatoryManagement extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $name;
    public $position;
    public $signature;
    public $is_active = true;
    
    public $editId = null;
    public $isEditing = false;
    public $showModal = false;
    public $showDeleteModal = false;
    public $deleteId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'signature' => 'nullable|image|max:2048',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        // Check if there's an active signatory
        $activeSignatory = Signatory::where('is_active', true)->first();
        if ($activeSignatory && !$this->editId) {
            $this->is_active = false; // Default new entries to inactive
        }
    }

    public function render()
    {
        $signatories = Signatory::with('media')->latest()->paginate(10);
        return view('livewire.admin.management.signatory-management', [
            'signatories' => $signatories
        ]);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // If setting to active, deactivate all others
        if ($this->is_active) {
            Signatory::where('is_active', true)->update(['is_active' => false]);
        }

        $data = [
            'name' => $this->name,
            'position' => $this->position,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing && $this->editId) {
            $signatory = Signatory::findOrFail($this->editId);
            
            // Update signatory info
            $signatory->update($data);
            
            // Update signature if new one is uploaded
            if ($this->signature) {
                $signatory->clearMediaCollection('signatures');
                $signatory->addSignature($this->signature);
            }
            
            session()->flash('success', 'Signatory updated successfully!');
        } else {
            $signatory = Signatory::create($data);
            
            // Add signature if uploaded
            if ($this->signature) {
                $signatory->addSignature($this->signature);
            }
            
            session()->flash('success', 'Signatory added successfully!');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        $signatory = Signatory::with('media')->findOrFail($id);
        $this->editId = $id;
        $this->isEditing = true;
        $this->name = $signatory->name;
        $this->position = $signatory->position;
        $this->is_active = $signatory->is_active;
        $this->showModal = true;
    }

    public function toggleActive($id)
    {
        $signatory = Signatory::findOrFail($id);
        
        if ($signatory->is_active) {
            $signatory->update(['is_active' => false]);
            session()->flash('info', 'Signatory deactivated!');
        } else {
            // Deactivate all others first
            Signatory::where('is_active', true)->update(['is_active' => false]);
            $signatory->update(['is_active' => true]);
            session()->flash('success', 'Signatory activated! Only one signatory can be active at a time.');
        }
        
        $this->resetPage();
    }

    public function deleteConfirm($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $signatory = Signatory::findOrFail($this->deleteId);
        
        // Delete signature media
        $signatory->deleteSignature();
        
        $signatory->delete();
        
        // If we deleted the active signatory, activate the first available one
        if ($signatory->is_active) {
            $nextSignatory = Signatory::first();
            if ($nextSignatory) {
                $nextSignatory->update(['is_active' => true]);
                session()->flash('info', 'Another signatory has been automatically activated.');
            }
        }
        
        $this->showDeleteModal = false;
        session()->flash('success', 'Signatory deleted successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'position',
            'signature',
            'is_active',
            'editId',
            'isEditing'
        ]);
        
        // Check if there's already an active signatory
        $activeSignatory = Signatory::where('is_active', true)->first();
        if ($activeSignatory) {
            $this->is_active = false; // Default new entries to inactive
        }
        
        $this->resetErrorBag();
    }
}