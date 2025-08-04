<?php

namespace App\Livewire\Admin\Semester;

use App\Models\Semester;
use Livewire\Component;

class SemesterIndex extends Component
{
    public $name;
    public $start_date;
    public $end_date;
    public $is_active = false;
    public $editMode = false;
    public $semesterId;

    protected $rules = [
        'name' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ];

    public function render()
    {
        $semesters = Semester::latest()->get();
        return view('livewire.admin.semester.semester-index', compact('semesters'));
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];

        if ($this->is_active) {
            Semester::query()->update(['is_active' => false]);
            $data['is_active'] = true;
        }

        if ($this->editMode) {
            $semester = Semester::findOrFail($this->semesterId);
            $semester->update($data);
            session()->flash('success', 'Semester updated successfully');
        } else {
            Semester::create($data);
            session()->flash('success', 'Semester created successfully');
        }

        $this->resetForm();
    }

    public function edit($id)
    {
        $semester = Semester::findOrFail($id);
        $this->semesterId = $id;
        $this->name = $semester->name;
        $this->start_date = $semester->start_date;
        $this->end_date = $semester->end_date;
        $this->is_active = $semester->is_active;
        $this->editMode = true;
    }

    public function delete($id)
    {
        Semester::findOrFail($id)->delete();
        session()->flash('success', 'Semester deleted successfully');
    }

    public function setActive($id)
    {
        Semester::query()->update(['is_active' => false]);
        $semester = Semester::findOrFail($id);
        $semester->update(['is_active' => true]);
        session()->flash('success', 'Semester activated successfully');
    }

    private function resetForm()
    {
        $this->reset(['name', 'start_date', 'end_date', 'is_active', 'editMode', 'semesterId']);
    }
}