<?php

namespace App\Mail;

use App\Models\Compra;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrdenCompraProveedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Compra $compra)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('Orden de compra #' . $this->compra->id)
            ->view('emails.orden-compra-proveedor');
    }
}
