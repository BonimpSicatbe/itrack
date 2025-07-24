<?php

namespace App\Livewire\Admin\FileManager;

use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileManagerIndex extends Component
{
    public function render()
    {
        $files = Media::all();

        return view('livewire.admin.file-manager.file-manager-index', [
            'files' => $files,
        ]);
    }
}
