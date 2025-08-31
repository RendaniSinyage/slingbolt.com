<?php

namespace App\Listeners;

use App\Events\DeleteCustomQuestion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCustomQuestionListener
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
     * @param  \App\Events\DeleteCustomQuestion  $event
     * @return void
     */
    public function handle(DeleteCustomQuestion $event)
    {
        //
    }
}
