<?php

namespace App\View\Components\user;

use App\Models\File;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Navigation extends Component
{
    public $navLink;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->navLink = [
            ['label' => 'Dashboard', 'route' => 'user.dashboard', 'icon' => 'home'],
            ['label' => 'Pending Requirements', 'route' => 'user.pending-task', 'icon' => 'spinner'],
            ['label' => 'Requirements', 'route' => 'user.archive', 'icon' => 'clipboard-list'],
            ['label' => 'Recents', 'route' => 'user.recents', 'icon' => 'clock'],
            ['label' => 'Archive', 'route' => 'user.archive', 'icon' => 'archive'],
            ['label' => 'File Manager', 'icon' => 'folder', 'route' => 'user.file-manager'],

            ['label' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'route' => 'profile.edit', 'icon' => 'user'],
            ['label' => 'Logout', 'route' => 'logout', 'icon' => 'right-from-bracket'],
        ];
    }

    public function render(): View|Closure|string
    {
        return view('layouts.user.navigation', [
            'navLinks' => $this->navLink,
        ]);
    }
}
