<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

Artisan::command('fielddesk:agent', function () {
    $account = [
        'name' => $this->ask('Agent name'),
        'email' => strtolower(trim((string) $this->ask('Agent email'))),
        'password' => $this->secret('Password (at least 12 characters)'),
    ];

    $validator = Validator::make($account, [
        'name' => 'required|string|max:120',
        'email' => 'required|email:rfc|max:254|unique:users,email',
        'password' => 'required|string|min:12|max:72',
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $message) {
            $this->error($message);
        }

        return 1;
    }

    User::create($account);
    $this->info('Agent account created.');

    return 0;
})->purpose('Provision a private Fielddesk agent account');
