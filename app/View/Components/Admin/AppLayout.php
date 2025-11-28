<?php

namespace App\View\Components\admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AppLayout extends Component
{

    public function render(): View|Closure|string
    {
        return view('components.admin.app-layout');
    }
}
