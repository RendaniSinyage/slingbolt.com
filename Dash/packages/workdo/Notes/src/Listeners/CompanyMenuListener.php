<?php

namespace Workdo\Notes\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Notes';
        $menu = $event->menu;
        $menu->add([
            'category' => 'Productivity',
            'title' => __('Notes'),
            'icon' => 'calendar-event',
            'name' => 'notes',
            'parent' => null,
            'order' => 1350,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'notes.index',
            'module' => $module,
            'permission' => 'note manage'
        ]);
    }
}
