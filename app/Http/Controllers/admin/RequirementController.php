<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Requirement;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return dd('success');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Requirement $requirement)
    {
        return view('admin.pages.requirement.show', [
            'requirement' => $requirement,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Requirement $requirement)
    {
        return view('admin.pages.requirement.edit', [
            'requirement' => $requirement,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'due' => 'required|date|after_or_equal:today',
            'required_files' => 'required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar,7z,mp4,avi,mkv,mp3,wav',
            'target' => 'required|in:college,department',
            'target_id' => 'required|integer',
        ]);

        $requirement->update($validated);

        return redirect()->route('admin.dashboard', $requirement)->with('success', 'Requirement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requirement $requirement)
    {
        //
    }
}
