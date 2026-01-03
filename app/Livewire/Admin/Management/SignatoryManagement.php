<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\Signatory;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class SignatoryManagement extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Sorting and search
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $search = '';

    // Modal states
    public $showModal = false;
    public $showDeleteModal = false;
    public $showPreviewModal = false;
    
    // Form data
    public $isEditing = false;
    public $editId = null;
    public $name = '';
    public $position = '';
    public $signature = null;
    public $is_active = false;
    public $signaturePreview = null;
    public $previewImage = null;
    
    // Delete confirmation
    public $signatoryToDelete = null;
    
    // Upload state
    public $uploadingSignature = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'signature' => 'nullable|image|max:2048', // 2MB max
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Signatory name is required.',
        'position.required' => 'Position is required.',
        'signature.image' => 'Signature must be an image file (JPG, PNG, GIF).',
        'signature.max' => 'Signature image must not exceed 2MB.',
    ];

    // Listen for signature upload
    public function updatedSignature()
    {
        $this->uploadingSignature = true;
        
        try {
            $this->validateOnly('signature');
            
            // Generate preview URL for temporary file
            if ($this->signature) {
                $this->signaturePreview = $this->signature->temporaryUrl();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload signature: ' . $e->getMessage());
        } finally {
            $this->uploadingSignature = false;
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->reset(['name', 'position', 'signature', 'signaturePreview', 'is_active', 'isEditing', 'editId', 'uploadingSignature']);
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'position', 'signature', 'signaturePreview', 'is_active', 'isEditing', 'editId', 'uploadingSignature']);
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $signatory = Signatory::findOrFail($id);
        
        $this->editId = $id;
        $this->name = $signatory->name;
        $this->position = $signatory->position;
        $this->is_active = $signatory->is_active;
        $this->isEditing = true;
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        try {
            // If activating a signatory, deactivate others
            if ($this->is_active) {
                Signatory::where('is_active', true)->update(['is_active' => false]);
            }

            if ($this->isEditing) {
                $signatory = Signatory::findOrFail($this->editId);
                $signatory->update([
                    'name' => $this->name,
                    'position' => $this->position,
                    'is_active' => $this->is_active,
                ]);

                // Update signature if provided
                if ($this->signature) {
                    $signatory->addSignature($this->signature);
                }
                
                session()->flash('success', 'Signatory updated successfully!');
            } else {
                $signatory = Signatory::create([
                    'name' => $this->name,
                    'position' => $this->position,
                    'is_active' => $this->is_active,
                ]);

                // Add signature if provided
                if ($this->signature) {
                    $signatory->addSignature($this->signature);
                }
                
                session()->flash('success', 'Signatory created successfully!');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save signatory: ' . $e->getMessage());
        }
    }

    public function deleteConfirm($id)
    {
        $this->signatoryToDelete = Signatory::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->signatoryToDelete) {
            try {
                // Check if active
                if ($this->signatoryToDelete->is_active) {
                    session()->flash('error', 'Cannot delete active signatory!');
                    $this->showDeleteModal = false;
                    return;
                }

                $signatoryName = $this->signatoryToDelete->name;
                $this->signatoryToDelete->delete();
                
                session()->flash('success', "Signatory '{$signatoryName}' deleted successfully!");
                $this->showDeleteModal = false;
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to delete signatory: ' . $e->getMessage());
            }
        }
    }

    public function toggleActive($id)
    {
        try {
            $signatory = Signatory::findOrFail($id);
            
            if ($signatory->is_active) {
                $signatory->update(['is_active' => false]);
                session()->flash('info', 'Signatory deactivated.');
            } else {
                // Deactivate all others first
                Signatory::where('is_active', true)->update(['is_active' => false]);
                
                // Activate this one
                $signatory->update(['is_active' => true]);
                session()->flash('success', 'Signatory activated successfully!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to toggle signatory status: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $signatories = Signatory::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.management.signatory-management', [
            'signatories' => $signatories,
        ]);
    }
}