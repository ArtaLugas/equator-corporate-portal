<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActivityItem extends Component
{
    public $title;

    public $description;

    public $time;

    public function __construct(
        $title,
        $description,
        $time
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->time = $time;
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.activity-item');
    }
}
