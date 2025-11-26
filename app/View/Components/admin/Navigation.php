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

    public function __construct()
    {
        $this->unreadCount = $this->getUnreadCount();
        $this->navLinks = $this->prepareNavigation();
    }

    protected function getUnreadCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Auth::user()->unreadNotifications()
            ->whereIn('type', [
                'App\Notifications\NewSubmissionNotification',
                'App\Notifications\SubmissionStatusUpdated',
                'App\Notifications\SemesterEndedWithMissingSubmissions' // ADD THIS
            ])
            ->count();
    }

    protected function prepareNavigation(): array
    {
        return [
            // Main navigation
            'main' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'th-large',
                    'route' => 'admin.dashboard',
                    'group' => 'admin.dashboard',
                    'description' => 'Overview and analytics'
                ],
                [
                    'label' => 'Requirements',
                    'icon' => 'clipboard-list',
                    'route' => 'admin.requirements.index',
                    'group' => 'admin.requirements.*',
                    'description' => 'Manage requirements'
                ],
                [
                    'label' => 'Submissions',
                    'icon' => 'paper-plane',
                    'route' => 'admin.submitted-requirements.index',
                    'group' => 'admin.submitted-requirements.*',
                    'description' => 'Review submissions'
                ],
                [
                    'label' => 'Portfolio',
                    'icon' => 'folder',
                    'route' => 'admin.file-manager.index',
                    'group' => 'admin.file-manager.*',
                    'description' => 'View User Portfolio'
                ],
                [
                    'label' => 'Reports',
                    'icon' => 'file-lines',
                    'route' => 'admin.reports.index',
                    'group' => 'admin.reports.*',
                    'description' => 'View system reports'
                ],
            ],
            // Secondary navigation (dropdown menu)
            'secondary' => [
                [
                    'label' => 'Management',
                    'icon' => 'gears',
                    'route' => 'admin.management.index',
                    'group' => 'admin.management.*',
                    'description' => 'System management'
                ],
                [
                    'label' => 'Notifications',
                    'icon' => 'bell',
                    'route' => 'admin.notifications',
                    'group' => 'admin.notifications',
                    'badge' => $this->unreadCount,
                    'description' => 'View notifications'
                ],
                [
                    'label' => 'Account',
                    'icon' => 'user-circle',
                    'route' => 'profile.edit',
                    'group' => 'profile.*',
                    'description' => 'Profile settings'
                ],
                [
                    'label' => 'Logout',
                    'icon' => 'right-from-bracket',
                    'route' => 'logout',
                    'group' => 'logout',
                    'description' => 'Sign out',
                    'type' => 'form'
                ],
            ]
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.navigation', [
            'navLinks' => $this->navLinks,
            'unreadCount' => $this->unreadCount
        ]);
    }
}
