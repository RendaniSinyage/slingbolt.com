<?php

namespace App\Listeners;

use App\Events\CreateCustomQuestion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateCustomQuestionListener
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
     * @param  \App\Events\CreateCustomQuestion  $event
     * @return void
     */
    public function handle(CreateCustomQuestion $event)
    {
        //
    }
}
