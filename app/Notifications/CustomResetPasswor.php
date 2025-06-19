<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class CustomResetPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return "http://127.0.0.1:8080/reset-password?token={$this->token}&email=" .
               urlencode($notifiable->getEmailForPasswordReset());
    }
}