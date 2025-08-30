<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class CreatePaymentInvoice
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $invoice;
    public $invoicePayment;
    public $request;
    public function __construct(Invoice $invoice, InvoicePayment $invoicePayment, Request $request)
    {
        $this->invoice = $invoice;
        $this->invoicePayment = $invoicePayment;
        $this->request = $request;
    }
}
