<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubmittedRequirement;
use Illuminate\Http\Request;

class SubmittedRequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.pages.submitted-requirements.submitted-requirements_index');
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SubmittedRequirement $submittedRequirement)
    {
        // Get the first media file if exists
        $firstFile = $submittedRequirement->media->first();
        
        return view('admin.pages.submitted-requirements.submitted-requirement_show', [
            'submittedRequirement' => $submittedRequirement,
            'initialFileId' => $firstFile ? $firstFile->id : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubmittedRequirement $submittedRequirement)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubmittedRequirement $submittedRequirement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubmittedRequirement $submittedRequirement)
    {
        //
    }
}
