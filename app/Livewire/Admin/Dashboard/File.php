<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class File extends Component
{
    public function render()
    {
        $media = Media::all();
        return view('livewire.admin.dashboard.file', [
            'media' => $media,
        ]);
    }
}
