<?php

namespace App\Console\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use Throwable;

class ConfigureLocalMysql extends Command
{
    protected $signature = 'fielddesk:mysql';

    protected $description = 'Move local Fielddesk data from SQLite to a dedicated MySQL 8 database';

    public function handle(): int
    {
        if (! app()->environment('local') || config('database.default') !== 'sqlite' || app()->configurationIsCached()) {
            $this->error('Run this in a local Fielddesk installation using SQLite and uncached configuration.');

            return self::FAILURE;
        }

        $database = trim((string) $this->ask('Existing MySQL database', 'fielddesk'));
        $username = trim((string) $this->ask('MySQL user for that database', 'fielddesk'));
        $password = $this->secret('MySQL password (input is hidden)') ?? '';

        if ($database === '' || $username === '') {
            $this->error('A database name and user are required.');

            return self::FAILURE;
        }

        $settings = [
            'DB_CONNECTION' => 'mysql',
            'DB_URL' => '',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ];

        $path = app()->environmentFilePath();
        $environment = File::get($path);
        foreach ($settings as $key => $value) {
            $encoded = '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
            $line = $key.'='.$encoded;

            try {
                $roundTrip = Dotenv::parse($line)[$key] ?? null;
            } catch (Throwable) {
                $roundTrip = null;
            }

            if ($roundTrip !== $value || preg_match('/[\x00-\x1f\x7f]/', $value)) {
                $this->error('One of the values cannot be written safely to the environment file.');

                return self::FAILURE;
            }

            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $environment = preg_match($pattern, $environment)
                ? preg_replace_callback($pattern, fn () => $line, $environment)
                : rtrim($environment).PHP_EOL.$line.PHP_EOL;
        }

        config(['database.connections.fielddesk_mysql' => array_replace(config('database.connections.mysql'), [
            'url' => null, 'host' => '127.0.0.1', 'port' => 3306, 'unix_socket' => '',
            'database' => $database, 'username' => $username, 'password' => $password,
            'read' => null, 'write' => null,
        ])]);

        try {
            $mysql = DB::connection('fielddesk_mysql');
            $version = (string) $mysql->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (Throwable $exception) {
            $this->error('MySQL connection failed ('.$exception->getCode().'). Check the server, database, user, and password.');

            return self::FAILURE;
        }

        if (! str_starts_with($version, '8.') || stripos($version, 'MariaDB') !== false) {
            $this->error('The server did not identify itself as MySQL 8.');

            return self::FAILURE;
        }

        if (! $this->confirm('Is the Fielddesk server stopped and this database reserved for Fielddesk?')) {
            return self::FAILURE;
        }

        $tables = ['users', 'clients', 'policies', 'policy_documents', 'contacts', 'follow_ups', 'agent_introductions'];

        try {
            if ($this->call('migrate', ['--database' => 'fielddesk_mysql', '--force' => true]) !== self::SUCCESS) {
                return self::FAILURE;
            }

            foreach ($tables as $table) {
                if ($mysql->table($table)->exists()) {
                    $this->error('MySQL already has Fielddesk data. The current connection and both databases were kept.');

                    return self::FAILURE;
                }
            }

            $sqlite = DB::connection('sqlite');
            $sqlite->transaction(fn () => $mysql->transaction(function () use ($sqlite, $mysql, $tables) {
                foreach ($tables as $table) {
                    $sqlite->table($table)->chunkById(100, function ($rows) use ($mysql, $table) {
                        $mysql->table($table)->insert($rows->map(fn ($row) => (array) $row)->all());
                    });
                }
            }));
            File::replace($path, $environment);
        } catch (Throwable $exception) {
            $this->error('The switch could not finish ('.$exception->getCode().'). The SQLite database is still available.');

            return self::FAILURE;
        }

        $this->info('Fielddesk now uses MySQL 8. The SQLite database was retained. Start Fielddesk and sign in again.');

        return self::SUCCESS;
    }
}
