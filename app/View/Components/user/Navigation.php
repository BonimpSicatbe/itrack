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
    public $bottomNavLink;
    public $logos; // Add this property

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        // Main navigation links
        $this->navLink = [
            ['label' => 'Dashboard', 'route' => 'user.dashboard', 'icon' => 'home'],
            ['label' => 'Requirements', 'route' => 'user.requirements', 'icon' => 'clipboard-list'],
            ['label' => 'Recents', 'route' => 'user.recents', 'icon' => 'clock'],
            ['label' => 'File Manager', 'icon' => 'folder', 'route' => 'user.file-manager'],
            ['label' => 'Archive', 'route' => 'user.archive', 'icon' => 'archive'],
            ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'user.notifications'],
        ];

        // Bottom navigation links (profile and logout)
        $this->bottomNavLink = [
            ['label' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'route' => 'profile.edit', 'icon' => 'user', 'is_profile' => true],
            ['label' => 'Logout', 'route' => 'logout', 'icon' => 'right-from-bracket'],
        ];
        
        // Add logo configuration - different logos for each state
        $this->logos = [
            'collapsed' => asset('images/logo-1.png'),
            'expanded' => asset('images/logo-title.png'), // Different logo for expanded state
        ];
    }

    public function render(): View|Closure|string
    {
        return view('layouts.user.navigation', [
            'navLinks' => $this->navLink,
            'bottomNavLinks' => $this->bottomNavLink,
            'logos' => $this->logos, // Pass logos to the view
        ]);
    }
}