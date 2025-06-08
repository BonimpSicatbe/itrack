<?php

namespace App\View\Components\admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NavigationController extends Component
{
    public $navLink = [
        ['label' => 'Dashboard', 'icon' => 'th', 'route' => 'admin.dashboard']
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
        return view('components.admin.navigation-controller', [
            'navLink' => $this->navLink
        ]);
    }
}
