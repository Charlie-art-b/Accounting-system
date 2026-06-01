<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthenticateUser extends Command
{
    protected $signature = 'app:authenticate-user {email}';
    protected $description = 'Authenticate a user in the session';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found");
            return 1;
        }

        Auth::login($user);
        
        $this->info("User {$user->name} authenticated successfully");
        $this->info("You can now access /admin");
        
        return 0;
    }
}
