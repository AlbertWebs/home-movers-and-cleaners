<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('app:create-admin-user')]
#[Description('Create or promote an administrator using an interactive password prompt')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->ask('Administrator name');
        $email = $this->ask('Administrator email');
        $password = $this->secret('Password (minimum 12 characters)');

        if (! is_string($name) || trim($name) === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 12) {
            $this->error('Provide a name, valid email, and password of at least 12 characters.');

            return self::FAILURE;
        }

        User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make($password), 'is_admin' => true]);
        $this->info('Administrator account is ready.');

        return self::SUCCESS;
    }
}
