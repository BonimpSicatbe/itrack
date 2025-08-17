<?php

namespace App\View\Components\admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Navigation extends Component
{
    public $navLinks = [
        ['label' => 'Dashboard', 'icon' => 'th-large', 'route' => 'admin.dashboard'],
        ['label' => 'Requirements', 'icon' => 'clipboard-list', 'route' => 'admin.requirements.index'],
        ['label' => 'Submissions', 'icon' => 'paper-plane', 'route' => 'admin.submitted-requirements.index'],
        ['label' => 'Files', 'icon' => 'file', 'route' => 'admin.file-manager.index'],
        ['label' => 'Users', 'icon' => 'users', 'route' => 'admin.users.index'],
        ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'admin.notifications'],
        ['label' => 'Account', 'icon' => 'user-circle', 'route' => 'profile.edit'],
        ['label' => 'Logout', 'icon' => 'right-from-bracket', 'route' => 'logout', 'is_logout' => true],
    ];

    /**
     * Check if a nav link should be active
     */
    public function isActive($route): bool
    {
        $currentRoute = request()->route()->getName();
        
        // Exact match
        if ($currentRoute === $route) {
            return true;
        }
        
        // Handle parent routes (e.g., admin.submitted-requirements.*)
        $routeBase = str_replace('.index', '', $route);
        $currentBase = str_replace('.index', '', $currentRoute);
        
        if (str_starts_with($currentRoute, $routeBase.'.') || 
            str_starts_with($currentBase, $routeBase.'.')) {
            return true;
        }
        
        return false;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('layouts.admin.navigation', [
            'navLinks' => $this->navLinks,
            'isActive' => \Closure::fromCallable([$this, 'isActive'])
        ]);
    }
}