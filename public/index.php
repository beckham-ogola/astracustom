<?php
/**
 * AstraCampus - Front Controller
 * All requests are routed through this file (see .htaccess).
 */

// Show errors during development; disable display_errors in production.
error_reporting(E_ALL);
ini_set('display_errors', getenv('ASTRA_DEBUG') ? '1' : '0');

define('ASTRA_ROOT', dirname(__DIR__));

// -------- Autoload models & controllers --------
spl_autoload_register(function ($class) {
    $paths = [
        ASTRA_ROOT . '/models/' . $class . '.php',
        ASTRA_ROOT . '/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

require_once ASTRA_ROOT . '/includes/functions.php';
require_once ASTRA_ROOT . '/includes/auth.php'; // starts the session
require_once ASTRA_ROOT . '/includes/router.php';
require_once ASTRA_ROOT . '/config/mpesa.php';
require_once ASTRA_ROOT . '/includes/mpesa.php';

$router = new Router();
require ASTRA_ROOT . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
