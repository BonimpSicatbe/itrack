<?php

namespace App\Http\Controllers\admin;

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
        $submittedRequirements = SubmittedRequirement::with(['requirement', 'user'])->get();

        return view('admin.pages.submitted-requirements.submitted-requirements-list', [
            'submittedRequirements' => $submittedRequirements,
        ]);
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
        return view('admin.pages.submitted-requirements.submitted-requirement-detail', [
            'submittedRequirement' => $submittedRequirement,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubmittedRequirement $submittedRequirement)
    {
        //
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
