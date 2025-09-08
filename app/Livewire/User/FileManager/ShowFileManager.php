<?php

namespace App\Livewire\User\FileManager;

use Livewire\Component;
use App\Models\SubmittedRequirement;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;

class ShowFileManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 12;
    public $viewMode = 'grid';
    public $selectedSemesterId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'selectedSemesterId' => ['except' => null],
    ];

    protected $listeners = [
        'refreshFiles' => '$refresh',
        'searchUpdated' => 'handleSearchUpdate',
        'statusFilterUpdated' => 'handleStatusFilterUpdate',
        'viewModeUpdated' => 'handleViewModeUpdate',
        'semesterChanged' => 'handleSemesterChange'
    ];

    public function mount($selectedSemesterId = null)
    {
        if ($selectedSemesterId) {
            $this->selectedSemesterId = $selectedSemesterId;
        } else {
            // Default to active semester
            $activeSemester = Semester::getActiveSemester();
            if ($activeSemester) {
                $this->selectedSemesterId = $activeSemester->id;
            }
        }
    }

    public function handleSearchUpdate($search)
    {
        $this->search = $search;
        $this->resetPage();
    }

    public function handleStatusFilterUpdate($statusFilter)
    {
        $this->statusFilter = $statusFilter;
        $this->resetPage();
    }

    public function handleViewModeUpdate($viewMode)
    {
        $this->viewMode = $viewMode;
    }

    public function handleSemesterChange($semesterId)
    {
        $this->selectedSemesterId = $semesterId;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function openFileDetails($submissionId)
    {
        // Emit event to parent component
        $this->dispatch('fileSelected', $submissionId);
    }

    public function deleteFile($submissionId)
    {
        $submission = SubmittedRequirement::where('user_id', Auth::id())
            ->findOrFail($submissionId);
        
        if (!$submission->canBeDeletedBy(Auth::user())) {
            session()->flash('error', 'You cannot delete this file.');
            return;
        }

        if ($submission->deleteFile()) {
            $submission->delete();
            session()->flash('success', 'File deleted successfully.');
            $this->dispatch('refreshFiles');
        } else {
            session()->flash('error', 'Failed to delete file.');
        }
    }

    public function getFilesProperty()
    {
        $query = SubmittedRequirement::where('user_id', Auth::id())
            ->with(['requirement', 'submissionFile', 'requirement.semester']);

        // Filter by selected semester if one is chosen
        if ($this->selectedSemesterId) {
            $query->whereHas('requirement', function($q) {
                $q->where('semester_id', $this->selectedSemesterId);
            });
        } else {
            // If no semester selected, show only active semester files
            $query->whereHas('requirement.semester', function($q) {
                $q->where('is_active', true);
            });
        }

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->whereHas('submissionFile', function($q2) {
                    $q2->where('file_name', 'like', '%' . $this->search . '%');
                })->orWhereHas('requirement', function($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        $files = $this->files;

        return view('livewire.user.file-manager.show-file-manager', [
            'files' => $files,
            'statuses' => SubmittedRequirement::statuses(),
        ]);
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
}