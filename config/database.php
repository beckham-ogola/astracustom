<?php
/**
 * AstraCampus - Database Configuration
 * Edit these constants to match your environment.
 */

define('DB_HOST', getenv('ASTRA_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('ASTRA_DB_NAME') ?: 'astracampus');
define('DB_USER', getenv('ASTRA_DB_USER') ?: 'root');
define('DB_PASS', getenv('ASTRA_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO connection.
 * @return PDO
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('DB Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please check config/database.php and try again.');
        }
    }

    return $pdo;
}
