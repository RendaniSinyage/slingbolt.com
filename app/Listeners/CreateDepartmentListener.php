<?php

namespace App\Listeners;

use App\Events\CreateDepartment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDepartmentListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CreateDepartment  $event
     * @return void
     */
    public function handle(CreateDepartment $event)
    {
        //
    }
}
