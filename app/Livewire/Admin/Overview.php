<?php

namespace App\Livewire\Admin;

use App\Models\File;
use Livewire\Component;

class Overview extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->stats = [
            [
                'title' => 'Total PDF Files',
                'count' => File::where('type', 'pdf')->orWhere('type', 'PDF')->count(),
                'desc' => File::where('type', 'pdf')->orWhere('type', 'PDF')->sum('size'),
                'icon' => 'fa-file-pdf',
                'color' => 'info',
            ],
            [
                'title' => 'Total Images',
                'count' => File::whereIn('type', ['image', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])->count(),
                'desc' => File::whereIn('type', ['image', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])->sum('size'),
                'icon' => 'fa-file-image',
                'color' => 'success',
            ],
            [
                'title' => 'Total Videos',
                'count' => File::whereIn('type', ['video', 'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'])->count(),
                'desc' => File::whereIn('type', ['video', 'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'])->sum('size'),
                'icon' => 'fa-file-video',
                'color' => 'warning',
            ],
            [
                'title' => 'Total PowerPoint Files',
                'count' => File::whereIn('type', ['ppt', 'pptx', 'powerpoint'])->count(),
                'desc' => File::whereIn('type', ['ppt', 'pptx', 'powerpoint'])->sum('size'),
                'icon' => 'fa-file-powerpoint',
                'color' => 'accent',
            ],
            [
                'title' => 'Total Excel Files',
                'count' => File::whereIn('type', ['excel', 'xls', 'xlsx'])->count(),
                'desc' => File::whereIn('type', ['excel', 'xls', 'xlsx'])->sum('size'),
                'icon' => 'fa-file-excel',
                'color' => 'secondary',
            ],
            [
                'title' => 'Total Word Files',
                'count' => File::whereIn('type', ['docs', 'doc', 'docx', 'word'])->count(),
                'desc' => File::whereIn('type', ['docs', 'doc', 'docx', 'word'])->sum('size'),
                'icon' => 'fa-file-word',
                'color' => 'primary',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.overview', [
            'stats' => $this->stats,
            'totalFiles' => File::sum('size'),
        ]);
    }
}
