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
            ['label' => 'Recents', 'route' => 'user.recents', 'icon' => 'clock-rotate-left'],
            ['label' => 'Requirements', 'route' => 'user.requirements', 'icon' => 'clipboard-list'],
            ['label' => 'Portfolio', 'icon' => 'folder', 'route' => 'user.file-manager'],
        ];

        // Bottom navigation links (profile and logout)
        $this->bottomNavLink = [
            ['label' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'route' => 'profile.edit', 'icon' => 'user', 'is_profile' => true],
            ['label' => 'Logout', 'route' => 'logout', 'icon' => 'right-from-bracket'],
        ];
        
        // Add logo configuration - different logos for each state
        $this->logos = [
            'collapsed' => asset('images/logo-1.png'),
            'expanded' => asset('images/logo-title.png'), 
        ];
    }

    public function render(): View|Closure|string
    {
        return view('layouts.user.navigation', [
            'navLinks' => $this->navLink,
            'bottomNavLinks' => $this->bottomNavLink,
            'logos' => $this->logos, 
        ]);
    }
}