<?php

namespace App\View\Components\Admin\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toggle extends Component
{
    public function __construct(
        public string $name,
        public ?string $label = null,
        public bool $checked = false,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.admin.form.toggle');
    }
}
