<?php

namespace App\Listeners;

use App\Events\CreateBankAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateBankAccountListener
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
     * @param  \App\Events\CreateBankAccount  $event
     * @return void
     */
    public function handle(CreateBankAccount $event)
    {
        //
    }
}
