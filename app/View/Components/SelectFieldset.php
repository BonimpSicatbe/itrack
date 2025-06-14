<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectFieldset extends Component
{
    public $label;
    public $name;
    public $placeholder;

    /**
     * Create a new component instance.
     */
    public function __construct($label = null, $name = null, $placeholder = null)
    {
        $this->label = $label ?? '';
        $this->name = $name ?? '';
        $this->placeholder = $placeholder ?? '';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.select-fieldset');
    }
}
