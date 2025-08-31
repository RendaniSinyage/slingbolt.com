<?php

namespace App\Listeners;

use App\Events\DeleteBankAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteBankAccountListener
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
     * @param  \App\Events\DeleteBankAccount  $event
     * @return void
     */
    public function handle(DeleteBankAccount $event)
    {
        //
    }
}
