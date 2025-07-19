<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requirements = Requirement::where('assigned_to', Auth::user()->college->name)
            ->orWhere('assigned_to', Auth::user()->department->name)
            ->get();

        return view('user.pages.requirement.index', [
            'requirements' => $requirements,
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
    public function show(Requirement $requirement)
    {
        return view('user.pages.requirement.show', [
            'requirement' => $requirement,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Requirement $requirement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Requirement $requirement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requirement $requirement)
    {
        //
    }
}
