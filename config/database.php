<?php

use Illuminate\Support\Str;

$sqliteDatabase = env('DB_DATABASE');

if ($sqliteDatabase === '' || $sqliteDatabase === null || $sqliteDatabase === ':memory:') {
    $sqliteDatabase = $sqliteDatabase ?: database_path('database.sqlite');
} elseif (! Str::startsWith($sqliteDatabase, ['/', '~', 'file://', 'sqlite://'])
    && ! preg_match('/^[A-Za-z]:[\\\\\/]/', $sqliteDatabase)) {
    $sqliteDatabase = base_path($sqliteDatabase);
}

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'postgres'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_SCHEMA', 'public'),
            'sslmode' => env('DB_SSLMODE', 'require'),
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => $sqliteDatabase,
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

    ],

    'migrations' => 'migrations',

];
