<?php

namespace App\View\Components\Admin\Form;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Wysiwyg extends Component
{
    public function __construct(

        public string $name,

        public ?string $label = null,

        public ?string $value = null,

        public ?string $placeholder = null,

    ) {}

    public function render(): View|Closure|string
    {
        return view('components.admin.form.wysiwyg');
    }
}
