<?php
declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Csrf\Guard;

return function (App $app) {
    $app->add(SessionMiddleware::class);

    $responseFactory = $app->getResponseFactory();

    // Register Middleware To Be Executed On All Routes
    $app->add(new Guard($responseFactory));
};
