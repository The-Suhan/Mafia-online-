<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('🎭 Reset Your Password — The Family Awaits')
            ->greeting('A Message from the Don')
            ->line('Word on the street is you forgot your password. Even the sharpest minds in the Family slip up sometimes.')
            ->line('If this was you, click below to set a new password and get back to the table.')
            ->action('Reset My Password', $url)
            ->line("This link expires in {$expireMinutes} minutes — choose wisely. After that, you'll have to come asking again.")
            ->line('If you didn\'t request this, sit tight. Nobody\'s coming for you — your secrets stay buried.')
            ->salutation("Stay in the shadows,\nThe Mafia Team");
    }

    protected function resetUrl(mixed $notifiable): string
    {
        return config('app.frontend_url', config('app.url'))
            .'/reset-password?token='
            .$this->token
            .'&email='
            .urlencode($notifiable->getEmailForPasswordReset());
    }
}
