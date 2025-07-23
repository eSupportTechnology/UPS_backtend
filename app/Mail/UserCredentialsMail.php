<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Your Account Credentials')
            ->view('emails.user_credentials')
            ->with([
                'name' => $this->user->name,
                'email' => $this->user->email,
                'password' => $this->password,
                'role' => $this->user->role_as,
            ]);
    }
}
