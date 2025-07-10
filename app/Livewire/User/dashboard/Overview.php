<?php

namespace App\Livewire\User\Dashboard;

use App\Models\SubmittedRequirement;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Overview extends Component
{
    public $stats = [];
    public $totalFilesSize = 0;

    public function mount()
    {
        $userId = Auth::id();
        
        $submittedRequirements = SubmittedRequirement::where('user_id', $userId)
            ->with('media')
            ->get();

        // Initialize counters for each file type
        $fileStats = [
            'Images' => ['size' => 0, 'count' => 0],
            'Videos' => ['size' => 0, 'count' => 0],
            'Documents' => ['size' => 0, 'count' => 0], // Word/PDF
            'PowerPoint' => ['size' => 0, 'count' => 0], // PowerPoint files
            'Spreadsheets' => ['size' => 0, 'count' => 0], // Excel files
        ];

        foreach ($submittedRequirements as $requirement) {
            foreach ($requirement->media as $media) {
                $sizeInMB = $media->size / (1024 * 1024);
                $mimeType = $media->mime_type;

                if (str_starts_with($mimeType, 'image/')) {
                    $fileStats['Images']['size'] += $sizeInMB;
                    $fileStats['Images']['count']++;
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $fileStats['Videos']['size'] += $sizeInMB;
                    $fileStats['Videos']['count']++;
                } elseif ($mimeType === 'application/vnd.ms-excel' || 
                        $mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                    $fileStats['Spreadsheets']['size'] += $sizeInMB;
                    $fileStats['Spreadsheets']['count']++;
                } elseif ($mimeType === 'application/vnd.ms-powerpoint' || 
                        $mimeType === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                    $fileStats['PowerPoint']['size'] += $sizeInMB;
                    $fileStats['PowerPoint']['count']++;
                } else {
                    // Default documents (Word, PDF)
                    $fileStats['Documents']['size'] += $sizeInMB;
                    $fileStats['Documents']['count']++;
                }

                $this->totalFilesSize += $sizeInMB;
            }
        }

        $this->stats = [
            [
                'title' => 'Images',
                'count' => $fileStats['Images']['count'],
                'desc' => round($fileStats['Images']['size'], 2),
                'icon' => 'fa-image',
                'icon_color' => 'text-blue-500',
                'bg_color' => 'bg-blue-50',
            ],
            [
                'title' => 'Videos',
                'count' => $fileStats['Videos']['count'],
                'desc' => round($fileStats['Videos']['size'], 2),
                'icon' => 'fa-video',
                'icon_color' => 'text-red-500',
                'bg_color' => 'bg-red-50',
            ],
            [
                'title' => 'Documents',
                'count' => $fileStats['Documents']['count'],
                'desc' => round($fileStats['Documents']['size'], 2),
                'icon' => 'fa-file-alt',
                'icon_color' => 'text-purple-500',
                'bg_color' => 'bg-purple-50',
            ],
            [
                'title' => 'PowerPoint',
                'count' => $fileStats['PowerPoint']['count'],
                'desc' => round($fileStats['PowerPoint']['size'], 2),
                'icon' => 'fa-file-powerpoint',
                'icon_color' => 'text-orange-500',
                'bg_color' => 'bg-orange-50',
            ],
            [
                'title' => 'Spreadsheets',
                'count' => $fileStats['Spreadsheets']['count'],
                'desc' => round($fileStats['Spreadsheets']['size'], 2),
                'icon' => 'fa-file-excel',
                'icon_color' => 'text-green-500',
                'bg_color' => 'bg-green-50',
            ],
            [
                'title' => 'Total',
                'count' => '',
                'desc' => round($this->totalFilesSize, 2),
                'icon' => 'fa-database',
                'icon_color' => 'text-indigo-500',
                'bg_color' => 'bg-indigo-50',
                'is_total' => true,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.user.dashboard.overview', [
            'totalFiles' => round($this->totalFilesSize, 2),
        ]);
    }
}