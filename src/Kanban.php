<?php

namespace Pablomadariaga\Kanban;

use Illuminate\View\ComponentAttributeBag;
use Pablomadariaga\Kanban\Foundation\Support\Blade\Directives;

class Kanban
{
    /**
     * Create an instance of the BladeDirectives class.
     */
    public function directives(): Directives
    {
        return app(Directives::class);
    }
}
