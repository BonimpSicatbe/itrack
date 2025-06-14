<?php

namespace App\Livewire\User\Dashboard;

use App\Models\File;
use Livewire\Component;

class Overview extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->stats = [
            [
                'title' => 'Images',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-image',
                'color' => 'success',
            ],
            [
                'title' => 'Videos',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-video',
                'color' => 'warning',
            ],
            [
                'title' => 'PowerPoint Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-powerpoint',
                'color' => 'accent',
            ],
            [
                'title' => 'Excel Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-excel',
                'color' => 'secondary',
            ],
            [
                'title' => 'Word Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-word',
                'color' => 'primary',
            ],
            [
                'title' => 'PDF Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-pdf',
                'color' => 'info',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.user.dashboard.overview', [
            'totalFiles' => '',
        ]);
    }
}
