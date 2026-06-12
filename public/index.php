<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PedalPal\Core\Router;
use PedalPal\Core\Response;
use PedalPal\Database\Migrator;

error_reporting(E_ALL);

// CLI commands
if (PHP_SAPI === 'cli') {
    $args = $argv ?? [];
    if (in_array('--migrate', $args, true)) {
        Migrator::migrate();
        Migrator::seed();
        exit;
    }
    if (in_array('--seed', $args, true)) {
        Migrator::seed();
        exit;
    }
    echo "Usage: php index.php [--migrate] [--seed]\n";
    exit;
}

// Handle preflight CORS
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

$router = new Router();
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// API routes
$router->get('/api/beach-cruisers', ['PedalPal\Controllers\BikeController', 'beachCruisers']);
$router->get('/api/mountain-bikes', ['PedalPal\Controllers\BikeController', 'mountainBikes']);
$router->post('/api/bikes/rent', ['PedalPal\Controllers\BikeController', 'rent']);
$router->post('/api/reset', ['PedalPal\Controllers\BikeController', 'reset']);
$router->get('/api/accessories', ['PedalPal\Controllers\AccessoryController', 'index']);
$router->post('/api/accessories/order', ['PedalPal\Controllers\AccessoryController', 'order']);

$router->dispatch($method, $uri);
