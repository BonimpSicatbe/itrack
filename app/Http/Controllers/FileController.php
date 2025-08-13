<?php

namespace App\Http\Controllers;

use App\Models\SubmittedRequirement;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function download(SubmittedRequirement $submission)
    {
        abort_unless($submission->submissionFile, 404);
        
        $filePath = $submission->getFilePath();
        abort_unless(file_exists($filePath), 404);

        return response()->download(
            $filePath,
            $submission->submissionFile->file_name
        );
    }

    public function preview(SubmittedRequirement $submission)
    {
        abort_unless($submission->submissionFile, 404);
        
        $filePath = $submission->getFilePath();
        abort_unless(file_exists($filePath), 404);

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
}