<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Bill;
use App\Models\BillPayment;
use Illuminate\Http\Request;

class CreateExpense
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $expense;
    public $expensePayment;
    public $request;
    public function __construct(Bill $expense, BillPayment $expensePayment, Request $request)
    {
        $this->expense = $expense;
        $this->expensePayment = $expensePayment;
        $this->request = $request;
    }
}
