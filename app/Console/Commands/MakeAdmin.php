<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'episteme:make-admin {email : Email of an existing user}';

    protected $description = 'Grant an existing user access to the admin panel';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user with that email. Register the account first, then run this again.');

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("{$user->email} can now sign in to /admin.");

        return self::SUCCESS;
    }
}
