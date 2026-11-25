<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class DevelopmentAgentSeeder extends Seeder
{
    public const EMAIL = 'agent@fielddesk.test';

    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('The development Agent can only be seeded in the local environment.');
        }

        $password = Str::random(24);
        $agent = User::firstOrCreate(['email' => self::EMAIL], [
            'name' => 'Fielddesk Test Agent',
            'password' => $password,
        ]);

        if ($agent->role !== 'agent') {
            throw new RuntimeException('This address belongs to a different account role. The account was kept.');
        }

        if (! $agent->wasRecentlyCreated) {
            $this->command?->info('The development Agent already exists. Its password was kept.');

            return;
        }

        $this->command?->info('Email: '.self::EMAIL);
        $this->command?->info('Password: '.$password);
    }
}
