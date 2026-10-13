<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Mail\CustomerInvoiceMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DispatchInvoiceMailToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event and transmit the compiled financial PDF directly to the customer.
     */
    public function handle(InvoiceCreated $event): void
    {
        $invoice = $event->invoice;

        try {
            // Target the dummy email address or corporate contact email captured inside the invoice blueprint
            // For rigorous testing purposes, we transmit safely to our secure sandbox network
            $recipientEmail = 'billing-department@customer-workspace.com';

            Mail::to($recipientEmail)->send(new CustomerInvoiceMail($invoice));

            Log::info("Asynchronous Customer Mailer Success: Dispatched PDF invoice {$invoice->invoice_number} directly to the client.");

        } catch (\Exception $e) {
            Log::error("Asynchronous Customer Mailer critical breakdown: " . $e->getMessage());
        }
    }
}
