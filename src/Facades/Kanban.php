<?php

namespace Pablomadariaga\Kanban\Facades;

use Illuminate\Support\Facades\Facade;

/**

 * @method static \Pablomadariaga\Kanban\Foundation\Support\Blade\Directives directives()
 *
 * @see \Pablomadariaga\Kanban
 */
class Kanban extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Kanban';
    }
}
