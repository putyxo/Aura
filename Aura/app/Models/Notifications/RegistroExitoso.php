<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RegistroExitoso extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        if ($notifiable->es_artista) {
            return (new MailMessage)
                ->subject('¡Bienvenido a Aura, Artista!')
                ->greeting('Hola ' . $notifiable->nombre_artistico . ' 🎤')
                ->line('Gracias por unirte a Aura como artista. Ahora puedes compartir tu música con el mundo.')
                ->action('Sube tu música', url('/dashboard'))
                ->line('¡Estamos emocionados de tenerte con nosotros!');
        } else {
            return (new MailMessage)
                ->subject('¡Bienvenido a Aura!')
                ->greeting('Hola ' . $notifiable->nombre . ' 👋')
                ->line('Gracias por registrarte en Aura. Ya puedes comenzar a explorar y disfrutar la música.')
                ->action('Ir a Aura', url('/dashboard'))
                ->line('¡Esperamos que disfrutes tu experiencia!');
        }
    }
}
