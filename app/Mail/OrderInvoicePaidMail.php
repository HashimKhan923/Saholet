<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderInvoicePaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public Order $order,
        public Payment $payment,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.invoices_from'), config('app.name')),
            subject: 'Your invoice from ' . config('app.name') . ' — ' . $this->invoice->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-invoice-paid',
            with: [
                'invoice' => $this->invoice,
                'order' => $this->order,
                'payment' => $this->payment,
            ],
        );
    }

    public function attachments(): array
    {
        $this->invoice->loadMissing('items');

        $pdf = Pdf::loadView('invoices.document', ['invoice' => $this->invoice])->output();

        return [
            Attachment::fromData(fn () => $pdf, $this->invoice->reference . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
