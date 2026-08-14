<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodigoRecuperacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $codigo;

    public function __construct(string $codigo)
    {
        $this->codigo = $codigo;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de recuperação de senha',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.codigo-recuperacao',
        );
    }
}
