<?php

namespace App\View\Components\admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Navigation extends Component
{
    public $navLinks = [
        ['label' => 'Dashboard', 'icon' => 'th-large', 'route' => 'admin.dashboard'],
        ['label' => 'Tasks', 'icon' => 'list', 'route' => 'admin.dashboard'],
        ['label' => 'Pendings', 'icon' => 'spinner', 'route' => 'admin.dashboard'],
        ['label' => 'Account', 'icon' => 'user-circle', 'route' => 'profile.edit'],
        ['label' => 'Logout', 'icon' => 'right-from-bracket', 'route' => "route('logout')"],
    ];

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('layouts.admin.navigation', [
            'navLink' => $this->navLinks
        ]);
    }
}
