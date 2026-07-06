<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatCard extends Component
{
    public function __construct(
        public string $title,
        public int|string $value,
        public ?string $description = null,
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.stat-card');
    }
}