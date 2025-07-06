<?php

namespace App\Http\Controllers;

use App\Models\SubmittedRequirement;
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
}