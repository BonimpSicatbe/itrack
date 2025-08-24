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
        $this->unreadCount = $this->getUnreadCount();
        $this->navLinks = $this->prepareNavigation();
    }

    /**
     * Get unread notifications count
     */
    protected function getUnreadCount(): int
    {
        return Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
    }

    /**
     * Prepare navigation structure
     */
    protected function prepareNavigation(): array
    {
        return [
            // Main navigation
            'main' => [
                [
                    'label' => 'Dashboard', 
                    'icon' => 'th-large', 
                    'route' => 'admin.dashboard', 
                    'description' => 'Overview and analytics'
                ],
                [
                    'label' => 'Requirements', 
                    'icon' => 'clipboard-list', 
                    'route' => 'admin.requirements.index', 
                    'description' => 'Manage requirements'
                ],
                [
                    'label' => 'Submissions', 
                    'icon' => 'paper-plane', 
                    'route' => 'admin.submitted-requirements.index', 
                    'description' => 'Review submissions'
                ],
                [
                    'label' => 'Files', 
                    'icon' => 'file', 
                    'route' => 'admin.file-manager.index', 
                    'description' => 'File management'
                ],
                [
                    'label' => 'Users', 
                    'icon' => 'users', 
                    'route' => 'admin.users.index', 
                    'description' => 'User management'
                ],
            ],
            // Secondary navigation
            'secondary' => [
                [
                    'label' => 'Notifications', 
                    'icon' => 'bell', 
                    'route' => 'admin.notifications', 
                    'badge' => $this->unreadCount, 
                    'description' => 'View notifications'
                ],
                [
                    'label' => 'Account', 
                    'icon' => 'user-circle', 
                    'route' => 'profile.edit', 
                    'description' => 'Profile settings'
                ],
                [
                    'label' => 'Logout', 
                    'icon' => 'right-from-bracket', 
                    'route' => 'logout', 
                    'description' => 'Sign out', 
                    'type' => 'form'
                ],
            ]
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