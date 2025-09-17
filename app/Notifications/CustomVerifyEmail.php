<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    public function toMail($notifiable): MailMessage
    {
        // Generamos el enlace firmado de TU backend (API)
        $verificationUrl = $this->verificationUrl($notifiable);

        // (Opcional) Si quieres enviar al FRONT y que el front luego llame a la API:
        // $frontend = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        // $verificationUrl = $frontend.'/verificar-email?url='.urlencode($this->verificationUrl($notifiable));

        return (new MailMessage)
            ->subject('Verifica tu correo')
            ->greeting('Hola '.$notifiable->nombre)
            ->line('Por favor, verifica tu dirección de correo para activar todas las funciones.')
            ->action('Verificar email', $verificationUrl)
            ->line('Si no creaste esta cuenta, puedes ignorar este correo.');
    }

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
