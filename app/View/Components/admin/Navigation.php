<?php

namespace App\View\Components\admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class Navigation extends Component
{
    public $navLinks = [];
    public $unreadCount = 0;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->unreadCount = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
        
        $this->navLinks = [
            ['label' => 'Dashboard', 'icon' => 'th-large', 'route' => 'admin.dashboard'],
            ['label' => 'Requirements', 'icon' => 'clipboard-list', 'route' => 'admin.requirements.index'],
            ['label' => 'Submissions', 'icon' => 'paper-plane', 'route' => 'admin.submitted-requirements.index'],
            ['label' => 'Files', 'icon' => 'file', 'route' => 'admin.file-manager.index'],
            ['label' => 'Users', 'icon' => 'users', 'route' => 'admin.users.index'],

            ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'admin.notifications', 'badge' => $this->unreadCount],
            ['label' => 'Account', 'icon' => 'user-circle', 'route' => 'profile.edit'],
            ['label' => 'Logout', 'icon' => 'right-from-bracket', 'route' => "route('logout')"],
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('layouts.admin.navigation', [
            'navLinks' => $this->navLinks,
            'unreadCount' => $this->unreadCount
        ]);
    }
}