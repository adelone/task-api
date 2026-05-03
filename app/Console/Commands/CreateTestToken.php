<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:create-test-token')]
#[Description('Create and return test token')]
class CreateTestToken extends Command
{
    protected $signature = 'app:create-test-token {--email=test@example.com}';
    protected $description = 'Create test user and return token';

    public function handle()
    {
        $user = User::first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $token = $user->createToken('dev')->plainTextToken;

        $this->info('Token: ' . $token);

        return $token;
    }
}
