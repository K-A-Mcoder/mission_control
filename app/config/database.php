<?php

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
|
| All values are pulled from the .env file. The connection string (DSN)
| is assembled here based on the chosen driver so that nothing else in
| the framework needs to know how each driver formats its DSN.
|
| Supported drivers: sqlite, mysql, pgsql
|
*/

$driver   = env('DB_DRIVER', 'sqlite');
$host     = env('DB_HOST', '127.0.0.1');
$port     = env('DB_PORT', '3306');
$database = env('DB_DATABASE', 'database/db.sqlite');
$username = env('DB_USERNAME', '');
$password = env('DB_PASSWORD', '');

$dsn = match ($driver) {
    'sqlite' => 'sqlite:' . BASE_PATH . '/' . $database,
    'mysql'  => "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
    'pgsql'  => "pgsql:host={$host};port={$port};dbname={$database}",
    default  => throw new \InvalidArgumentException("Unsupported database driver: [{$driver}]"),
};

return [
    'driver'           => $driver,
    'connectionString' => $dsn,
    'username'         => $username,
    'password'         => $password,
];
