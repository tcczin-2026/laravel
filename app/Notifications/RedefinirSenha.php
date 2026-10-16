<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** E-mail (em português) com o link para criar uma nova senha */
class RedefinirSenha extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $link = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $minutos = config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Redefinição de senha - Colecao de Games')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Recebemos um pedido para redefinir a senha da sua conta.')
            ->action('Criar nova senha', $link)
            ->line("Este link expira em {$minutos} minutos.")
            ->line('Se não foi você que pediu, ignore este e-mail: sua senha continua a mesma.')
            ->salutation('Colecao de Games');
    }
}
