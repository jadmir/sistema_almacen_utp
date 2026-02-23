<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UsuarioCreado extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $passwordTemporal;

    /**
     * Create a new message instance.
     */
    public function __construct($usuario, $passwordTemporal)
    {
        $this->usuario = $usuario;
        $this->passwordTemporal = $passwordTemporal;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido al Sistema de Almacén UTP',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.usuario-creado',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
