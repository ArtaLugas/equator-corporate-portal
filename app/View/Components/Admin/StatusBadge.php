<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    public function __construct(
        public string $status
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.admin.status-badge');
    }
}
