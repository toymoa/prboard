<?php

declare(strict_types=1);

use App\Middleware\MethodOverrideMiddleware;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../config/settings.php';
$containerBuilder->addDefinitions($settings);

// Set up dependencies
$dependencies = require __DIR__ . '/../config/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register middleware
$app->addRoutingMiddleware();

// Add Method Override Middleware
$app->add(new MethodOverrideMiddleware());

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    $container->get('settings')['settings']['displayErrorDetails'],
    true,
    true
);

// Register routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

return $app;
