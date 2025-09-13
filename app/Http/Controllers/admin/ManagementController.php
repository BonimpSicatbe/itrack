<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function index(Request $request)
    {
        $tabs = [
            'semesters' => ['label' => 'Semesters', 'icon' => 'calendar'],
            'users' => ['label' => 'Users', 'icon' => 'users'],
            'colleges' => ['label' => 'Colleges', 'icon' => 'building-columns'],
            'departments' => ['label' => 'Departments', 'icon' => 'department'],
        ];

        $activeTab = $request->query('tab', 'semesters');

        return view('admin.pages.management.management-index', [
            'tabs' => $tabs,
            'activeTab' => $activeTab
        ]);
    }
}