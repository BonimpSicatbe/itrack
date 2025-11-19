<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SubmittedRequirement;
use App\Models\Requirement;
use App\Models\Semester;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.pages.users.users_index');
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
    public function show(Request $request, User $user)
    {
        // If it's an AJAX request, return JSON with the rendered HTML
        if ($request->ajax()) {
            $html = view('livewire.admin.users.user-show', compact('user'))->render();
            return response()->json(['html' => $html]);
        }

        // For regular requests, return the full view
        return view('admin.pages.users.show', compact('user'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.pages.users.users_edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    protected function authorizeMedia(MediaModel $media): void
    {
        $model = $media->model;

        if ($model instanceof SubmittedRequirement) {
            if ((int) $model->user_id !== (int) Auth::id()) {
                abort(Response::HTTP_FORBIDDEN, 'You do not have access to this file.');
            }
            return;
        }

        if ($model instanceof Requirement) {
            // Allowed: requirement-owned files (e.g., 'guides', 'requirements' collections)
            return;
        }

        abort(Response::HTTP_FORBIDDEN, 'Invalid file owner.');
    }

    /**
     * Preview file inline for images/PDF; otherwise download/redirect.
     * Route name: user.file.preview
     */
    public function preview($mediaId)
    {
        $media = MediaModel::query()->findOrFail($mediaId);
        $this->authorizeMedia($media);

        $mime    = $media->mime_type ?? '';
        $inline  = (function_exists('str_starts_with') ? str_starts_with($mime, 'image/') : strpos($mime, 'image/') === 0)
            || $mime === 'application/pdf';

        $path    = $media->getPath();     // absolute local path
        $fullUrl = $media->getFullUrl();  // public URL

        if ($inline) {
            if ($path && file_exists($path)) {
                return response()->file($path, [
                    'Content-Type'        => $mime ?: 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
                ]);
            }
            return redirect()->away($fullUrl);
        }

        if ($path && file_exists($path)) {
            return response()->download($path, $media->file_name);
        }
        return redirect()->away($fullUrl);
    }

    /**
     * Force download; fallback to public URL if stored remotely.
     * Route name: user.file.download
     */
    public function download($mediaId)
    {
        $media = MediaModel::query()->findOrFail($mediaId);
        $this->authorizeMedia($media);

        $path    = $media->getPath();
        $fullUrl = $media->getFullUrl();

        if ($path && file_exists($path)) {
            return response()->download($path, $media->file_name);
        }
        return redirect()->away($fullUrl);
    }
}