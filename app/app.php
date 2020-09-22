<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

/**
 * @param bool $production
 * @return App
 */
return function ($production = false)
{

    // Instantiate PHP-DI ContainerBuilder
    $containerBuilder = new ContainerBuilder();

    if ($production) { // Should be set to true in production
        $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
    }

    // Set up settings
    $settings = require __DIR__ . '/../app/settings.php';
    $settings($containerBuilder, $production);

    // Set up dependencies
    $dependencies = require __DIR__ . '/../app/dependencies.php';
    $dependencies($containerBuilder);

    // Set up repositories
    $repositories = require __DIR__ . '/../app/repositories.php';
    $repositories($containerBuilder);

    // Build PHP-DI Container instance
    $container = $containerBuilder->build();

    // Instantiate the app
    AppFactory::setContainer($container);
    return $app = AppFactory::create();
};