<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class CustomResetPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return "https://backend-alganis-production.up.railway.app/reset-password?token={$this->token}&email=" .
               urlencode($notifiable->getEmailForPasswordReset());
    }
}