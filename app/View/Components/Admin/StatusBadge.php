<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

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
