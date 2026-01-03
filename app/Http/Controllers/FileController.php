<?php

namespace App\Http\Controllers;

use App\Models\SubmittedRequirement;
use App\Models\Signatory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function download(SubmittedRequirement $submission)
    {
        abort_unless($submission->submissionFile, 404);
        
        // If approved and has signed document, download signed version
        if ($submission->status === 'approved' && $submission->has_signed_document) {
            return $this->downloadSigned($submission);
        }
        
        // Otherwise, download original file
        return $this->downloadOriginal($submission);
    }

    public function preview($submission)
    {
        $submission = SubmittedRequirement::findOrFail($submission);
        
        // Get the file to preview
        if ($submission->isApproved && $submission->has_signed_document) {
            $media = $submission->getFirstMedia('signed_documents');
        } else {
            $media = $submission->getFirstMedia('submission_files');
        }
        
        if (!$media) {
            abort(404, 'File not found');
        }
        
        $filePath = $media->getPath();
        
        if (!file_exists($filePath)) {
            abort(404, 'File does not exist');
        }
        
        return response()->file($filePath);
    }

    public function downloadOriginal(SubmittedRequirement $submission)
    {
        abort_unless($submission->submissionFile, 404);
        
        $filePath = $submission->getOriginalFilePath();
        abort_unless(file_exists($filePath), 404);

        return response()->download(
            $filePath,
            $submission->submissionFile->file_name
        );
    }

    public function previewOriginal($submission)
    {
        $submission = SubmittedRequirement::findOrFail($submission);
        
        $media = $submission->getFirstMedia('submission_files');
        
        if (!$media) {
            abort(404, 'Original file not found');
        }
        
        $filePath = $media->getPath();
        
        if (!file_exists($filePath)) {
            abort(404, 'File does not exist');
        }
        
        return response()->file($filePath);
    }

    public function downloadSigned(SubmittedRequirement $submission)
    {
        if (!$submission->has_signed_document) {
            abort(404, 'No signed document available');
        }
        
        // Get the signed document from media collection
        $media = $submission->getFirstMedia('signed_documents');
        
        if (!$media) {
            abort(404, 'Signed document not found');
        }
        
        return response()->download($media->getPath(), 'signed_' . $media->file_name);
    }

    public function previewSigned($submission)
    {
        $submission = SubmittedRequirement::findOrFail($submission);
        
        $media = $submission->getFirstMedia('signed_documents');
        
        if (!$media) {
            abort(404, 'Signed document not found');
        }
        
        $filePath = $media->getPath();
        
        if (!file_exists($filePath)) {
            abort(404, 'File does not exist');
        }
        
        return response()->file($filePath);
    }

    public function downloadGuide(Media $media)
    {
        abort_unless($media->collection_name === 'guides', 404);
        
        $filePath = $media->getPath();
        abort_unless(file_exists($filePath), 404);

        return response()->download(
            $filePath,
            $media->file_name
        );
    }

    public function previewGuide(Media $media)
    {
        abort_unless($media->collection_name === 'guides', 404);
        
        $filePath = $media->getPath();
        abort_unless(file_exists($filePath), 404);

        return response()->file($filePath);
    } 

    /**
     * Preview signatory signature
     */
    public function previewSignature($signatoryId)
    {
        $signatory = Signatory::findOrFail($signatoryId);
        
        if (!$signatory->has_signature) {
            abort(404, 'Signature not found');
        }
        
        $media = $signatory->getFirstMedia('signatures');
        
        if (!$media) {
            abort(404, 'Signature media not found');
        }
        
        // Check if the file exists
        if (!Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
            abort(404, 'Signature file not found');
        }
        
        $path = $media->getPath();
        $mime = $media->mime_type;
        
        // For images, return as inline content
        if (str_starts_with($mime, 'image/')) {
            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
            ]);
        }
        
        // For other file types, download
        return response()->download($path, $media->file_name);
    }

    /**
     * Download signatory signature
     */
    public function downloadSignature($signatoryId)
    {
        $signatory = Signatory::findOrFail($signatoryId);
        
        if (!$signatory->has_signature) {
            abort(404, 'Signature not found');
        }
        
        $media = $signatory->getFirstMedia('signatures');
        
        if (!$media) {
            abort(404, 'Signature media not found');
        }
        
        return response()->download(
            $media->getPath(),
            $signatory->name . '_signature.' . pathinfo($media->file_name, PATHINFO_EXTENSION)
        );
    }
    
}