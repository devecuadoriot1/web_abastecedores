<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $organizacionNombre,
        public string $email,
        public string $passwordTemporal
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $appUrl = config('app.url');

        return (new MailMessage)
            ->subject('Bienvenido(a) – Acceso al sistema')
            ->greeting('¡Hola!')
            ->line("Se ha registrado como nuevo usuario en {$this->organizacionNombre}.")
            ->line('Sus credenciales de acceso son:')
            ->line("Email: {$this->email}")
            ->line("Contraseña: {$this->passwordTemporal}")
            ->action('Acceder al sistema', $appUrl)
            ->line('Por seguridad, se le solicitará cambiar la contraseña en su primer inicio de sesión.')
            ->salutation('Gracias por preferirnos. Saludos,');
    }
}
