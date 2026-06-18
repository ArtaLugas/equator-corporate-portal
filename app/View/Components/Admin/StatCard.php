<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatCard extends Component
{
    public function __construct(
        public string $title,
        public string $value,
        public string $color = 'primary', // Pilihan: primary, bright, orange, success
        public ?string $trend = null,     // 'up' atau 'down'
        public ?string $delta = null,     // misal: '+12.5%'
        public ?string $sub = null,       // misal: 'dari bulan lalu'
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.admin.stat-card');
    }
}
