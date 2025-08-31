<?php

namespace App\Listeners;

use App\Events\UpdateCustomQuestion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCustomQuestionListener
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
     * @param  \App\Events\UpdateCustomQuestion  $event
     * @return void
     */
    public function handle(UpdateCustomQuestion $event)
    {
        //
    }
}
