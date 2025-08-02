<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        return view('admin.pages.semester.semester_index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Deactivate all other semesters if this one is being activated
        if ($request->is_active) {
            Semester::query()->update(['is_active' => false]);
        }

        Semester::create($validated);

        return redirect()->back()->with('success', 'Semester created successfully');
    }

    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Deactivate all other semesters if this one is being activated
        if ($request->is_active) {
            Semester::where('id', '!=', $semester->id)->update(['is_active' => false]);
        }

        $semester->update($validated);

        return redirect()->back()->with('success', 'Semester updated successfully');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();
        return redirect()->back()->with('success', 'Semester deleted successfully');
    }

    public function setActive($id)
    {
        Semester::query()->update(['is_active' => false]);
        $semester = Semester::findOrFail($id);
        $semester->update(['is_active' => true]);
        session()->flash('success', 'Semester activated successfully');
    }
}