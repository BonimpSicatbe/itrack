<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TextFieldset extends Component
{
    public $label;
    public $name;
    public $placeholder;
    public $type = 'text';

    /**
     * Create a new component instance.
     */
    public function __construct($label = null, $name = null, $placeholder = null, $type = 'text')
    {
        $this->label = $label ?? '';
        $this->name = $name ?? '';
        $this->placeholder = $placeholder ?? '';
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.text-fieldset');
    }
}
