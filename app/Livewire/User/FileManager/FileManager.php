<?php

namespace App\Livewire\User\FileManager;

use Livewire\Component;
use App\Models\SubmittedRequirement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManager extends Component
{
    public $search = '';
    public $showFileDetails = false;
    public $selectedFile = null;
    
    protected $listeners = ['refreshFiles' => '$refresh', 'fileSelected' => 'handleFileSelected'];

    public function updatedSearch()
    {
        // Trigger re-render when search changes
        $this->render();
    }

    public function handleFileSelected($submissionId)
    {
        $this->selectedFile = SubmittedRequirement::where('user_id', Auth::id())
            ->with(['requirement', 'submissionFile', 'user'])
            ->findOrFail($submissionId);
        
        $this->showFileDetails = true;
    }

    public function closeFileDetails()
    {
        $this->showFileDetails = false;
        $this->selectedFile = null;
    }

    public function downloadFile($submissionId)
    {
        try {
            $submission = SubmittedRequirement::where('user_id', Auth::id())
                ->with('submissionFile')
                ->findOrFail($submissionId);

            if (!$submission->submissionFile) {
                session()->flash('error', 'File record not found.');
                return;
            }

            $file = $submission->submissionFile;
            
            if (!$file->file_path) {
                session()->flash('error', 'File path is missing.');
                return;
            }

            $filePath = $file->file_path;
            
            // Check if file exists in storage
            if (!Storage::exists($filePath)) {
                session()->flash('error', 'File not found in storage: ' . $filePath);
                return;
            }

            // Get file contents
            $fileContents = Storage::get($filePath);
            $fileName = $file->file_name ?: 'download';
            
            // Try to get mime type, fallback to application/octet-stream
            try {
                $mimeType = Storage::mimeType($filePath);
            } catch (\Exception $e) {
                $mimeType = 'application/octet-stream';
            }

            // Create a streamed response for download
            return response()->streamDownload(function () use ($fileContents) {
                echo $fileContents;
            }, $fileName, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            session()->flash('error', 'Download failed: ' . $e->getMessage());
            return;
        }
    }

    public function canOpenFile($file)
    {
        if (!$file || !$file->submissionFile) {
            return false;
        }

        $extension = strtolower(pathinfo($file->submissionFile->file_name, PATHINFO_EXTENSION));
        $excludedTypes = ['xls', 'xlsx']; // Excel files cannot be opened directly in browser
        
        return !in_array($extension, $excludedTypes);
    }

    public function canDownloadFile($file)
    {
        if (!$file || !$file->submissionFile) {
            return false;
        }

        // If there's no file_path, we can't download
        if (!$file->submissionFile->file_path) {
            return false;
        }

        // For now, let's be more permissive - if we have a file record, allow download attempt
        // The actual file existence check will be done during download
        return true;
    }

    public function getFileUrl($file)
    {
        if (!$file || !$file->submissionFile) {
            return null;
        }

        return $file->getFileUrl();
    }

    public function getFileIcon($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return match($extension) {
            'pdf' => 'fa-file-pdf text-red-500',
            'doc', 'docx' => 'fa-file-word text-blue-500',
            'xls', 'xlsx' => 'fa-file-excel text-green-500',
            'ppt', 'pptx' => 'fa-file-powerpoint text-orange-500',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' => 'fa-file-image text-purple-500',
            'zip', 'rar', '7z' => 'fa-file-zipper text-yellow-500',
            'txt' => 'fa-file-lines text-gray-500',
            default => 'fa-file text-gray-500',
        };
    }

    public function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function render()
    {
        return view('livewire.user.file-manager.file-manager', [
            'totalFiles' => $this->getTotalFiles(),
            'totalSize' => $this->getTotalSize(),
        ]);
    }

    protected function getTotalFiles()
    {
        return SubmittedRequirement::where('user_id', Auth::id())
            ->whereHas('submissionFile')
            ->count();
    }

    protected function getTotalSize()
    {
        $submissions = SubmittedRequirement::where('user_id', Auth::id())
            ->with('submissionFile')
            ->get();
        
        $totalSize = 0;
        foreach ($submissions as $submission) {
            if ($submission->submissionFile) {
                $totalSize += $submission->submissionFile->size ?? 0;
            }
        }
        
        return $this->formatFileSize($totalSize);
    }
}