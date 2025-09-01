<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function index()
    {
        $tabs = [
            'users' => ['label' => 'Users', 'icon' => 'users'],
            'colleges' => ['label' => 'Colleges', 'icon' => 'building'],
            'departments' => ['label' => 'Departments', 'icon' => 'sitemap'],
            'settings' => ['label' => 'Settings', 'icon' => 'cog'],
        ];

        return view('admin.pages.management.management-index', [
            'tabs' => $tabs,
            'activeTab' => request()->query('tab', 'users')
        ]);
    }
}