<?php
declare(strict_types=1);

use App\Application\ResponseEmitter\ResponseEmitter;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;

session_start();

/** @var App $app */
$appBuilder = include __DIR__ . '/../app/app.php';
$app = $appBuilder();

$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Handle errors
$errors = require __DIR__ . '/../app/errors.php';
$errors($app, $request);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
