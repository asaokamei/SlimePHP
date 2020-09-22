<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder, $production = false) {
    $projectRoot = dirname(__DIR__);
    $settings = [
        'production' => $production,
        'projectRoot' => $projectRoot,
        'displayErrorDetails' => false, // Should be set to false in production
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : $projectRoot . '/logs/app.log',
            'level' => Logger::ERROR,
        ],
        'cache-path' => $projectRoot . '/var/cache/',
    ];
    if (!$production) {
        $settings = array_merge($settings, [
            'displayErrorDetails' => true, // Should be set to false in production
            'logger' => [
                'name' => 'slim-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $projectRoot . '/logs/app.log',
                'level' => Logger::DEBUG,
            ],
        ]);
    }
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => $settings,
    ]);
};
