<?php

namespace App\Listeners;

use App\Events\UpdateBankAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBankAccountListenerListener
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
     * @param  \App\Events\UpdateBankAccount  $event
     * @return void
     */
    public function handle(UpdateBankAccount $event)
    {
        //
    }
}
