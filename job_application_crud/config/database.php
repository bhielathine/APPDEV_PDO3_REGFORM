<?php
declare(strict_types=1);

/*
 * Update these settings to match your local MySQL setup.
 * Example database name: job_application_db
 */
$host = 'localhost';
$dbname = 'job_application_db';
$username = 'root';
$password = '';

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Avoid displaying database credentials or internal errors to visitors.
    exit('Database connection failed. Check config/database.php and make sure MySQL is running.');
}
