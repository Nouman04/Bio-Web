<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

/**
 * The "choose a new password" email.
 *
 * Laravel's own version renders through the framework's markdown shell, which
 * is generic and carries none of the site's identity. This sends the same token
 * through the branded template in resources/views/emails, so the message a
 * reader gets looks like it came from the site they asked it from.
 */
class ResetPassword extends BaseNotification
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Reset your password'))
            ->view('emails.reset-password', [
                'name' => $notifiable->name,
                'url' => $this->resetUrl($notifiable),
                'minutes' => $this->expiryMinutes(),
            ]);
    }

    /**
     * The link the reader follows. Same route the framework builds, so an
     * existing reset link keeps working.
     */
    protected function resetUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    /**
     * How long the link is good for, read off the broker rather than stated
     * twice — the email should never promise longer than the token lasts.
     */
    protected function expiryMinutes(): int
    {
        return (int) Config::get(
            'auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire',
            60
        );
    }
}
