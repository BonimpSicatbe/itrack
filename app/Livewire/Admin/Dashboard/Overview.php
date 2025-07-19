<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Overview extends Component
{
    public $stats = [];
    public $totalFiles = 0;

    public function mount()
    {
        $this->stats = [
            [
                'title' => 'Total PDF Files',
                'count' => Media::where('mime_type', 'application/pdf')->count(),
                'desc' => 0,
                'icon' => 'fa-file-pdf',
                'color' => 'info',
            ],
            [
                'title' => 'Total Images',
                'count' => Media::whereIn('mime_type', [
                    'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/svg+xml', 'image/webp'
                ])->count(),
                'desc' => 0,
                'icon' => 'fa-file-image',
                'color' => 'success',
            ],
            [
                'title' => 'Total Videos',
                'count' => Media::whereIn('mime_type', [
                    'video/mp4', 'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', 'video/x-matroska', 'video/webm'
                ])->count(),
                'desc' => 0,
                'icon' => 'fa-file-video',
                'color' => 'warning',
            ],
            [
                'title' => 'Total PowerPoint Files',
                'count' => Media::whereIn('mime_type', [
                    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                ])->count(),
                'desc' => 0,
                'icon' => 'fa-file-powerpoint',
                'color' => 'accent',
            ],
            [
                'title' => 'Total Excel Files',
                'count' => Media::whereIn('mime_type', [
                    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'
                ])->count(),
                'desc' => 0,
                'icon' => 'fa-file-excel',
                'color' => 'secondary',
            ],
            [
                'title' => 'Total Word Files',
                'count' => Media::whereIn('mime_type', [
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ])->count(),
                'desc' => 0,
                'icon' => 'fa-file-word',
                'color' => 'primary',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.overview', [
            'stats' => $this->stats,
        ]);
    }
}
