<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Overview extends Component
{
    public $stats = [];

    public function mount()
    {
        $user = Auth::user();
        $this->stats = [
            [
                'title' => 'Total PDF Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-pdf',
                'color' => 'info',
            ],
            [
                'title' => 'Total Images',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-image',
                'color' => 'success',
            ],
            [
                'title' => 'Total Videos',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-video',
                'color' => 'warning',
            ],
            [
                'title' => 'Total PowerPoint Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-powerpoint',
                'color' => 'accent',
            ],
            [
                'title' => 'Total Excel Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-excel',
                'color' => 'secondary',
            ],
            [
                'title' => 'Total Word Files',
                'count' => '',
                'desc' => '',
                'icon' => 'fa-file-word',
                'color' => 'primary',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.overview', [
            'stats' => $this->stats,
            'totalFiles' => '',
        ]);
    }
}
