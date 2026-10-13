<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The dynamic invitation model instance bound to the tenant workspace.
     */
    public Invoice $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        // Bind the contextual encryption token and receiver details
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope defining the international financial subject line.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Commercial Invoice Issued - ' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition targeting our dedicated pure English billing layout view.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customer_invoice',
            with: [
                'customerName' => $this->invoice->customer_name,
                'invoiceNumber' => $this->invoice->invoice_number,
                'grossAmount' => number_format($this->invoice->gross_amount, 2),
            ],
        );
    }

    /**
     * Compile the dynamic PDF layout in memory and bind it clean as an immutable email attachment.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Ensure child objects and corporate vat relates dictionaries are pre-loaded
        $this->invoice->load('items.vatRate');

        // Compile the DomPDF layout exactly like our HTTP download controller vector does
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', ['invoice' => $this->invoice]);
        $pdf->setPaper('a4', 'portrait');

        return [
            // Inject the raw compiled binary bytes stream directly into the SMTP email envelope frame
            Attachment::fromData(fn () => $pdf->output(), "Invoice-{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
