<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $roleName;

    public function __construct(User $user, string $password, string $roleName)
    {
        $this->user = $user;
        $this->password = $password;
        $this->roleName = $roleName;
    }

    public function build()
    {
        return $this->view('emails.user_credentials')
            ->subject('Welcome to ' . config('app.name', 'Our Platform') . ' - Your Account Details')
            ->with([
                'name' => $this->user->name,
                'email' => $this->user->email,
                'password' => $this->password,
                'role' => $this->roleName,
                'loginUrl' => config('app.url', 'http://localhost') . '/login'
            ]);
    }
}
