<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder, $production = false) {
    $settings = [
        'displayErrorDetails' => false, // Should be set to false in production
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => Logger::ERROR,
        ],
    ];
    if (!$production) {
        $settings = array_merge($settings, [
            'displayErrorDetails' => true, // Should be set to false in production
            'logger' => [
                'name' => 'slim-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
        ]);
    }
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => $settings,
    ]);
};
