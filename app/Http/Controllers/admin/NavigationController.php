<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public $navLink = [
        ['label' => 'Dashboard', 'icon' => 'th', 'route' => 'admin.dashboard']
    ];

    public function navigation()
    {
        return view('admin.navigation', [
            'navLink' => $this->navLink
        ]);
    }
}
