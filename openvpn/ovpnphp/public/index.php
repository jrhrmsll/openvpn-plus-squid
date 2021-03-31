<?php

require '../vendor/autoload.php';

session_start();

// instantiate the app
$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);

// dependency injection container
require __DIR__ . '/../app/bootstrap.php';

// register routes
require __DIR__ . '/../app/routes.php';

$app->run();
