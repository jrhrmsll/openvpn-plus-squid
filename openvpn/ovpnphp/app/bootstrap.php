<?php

$container = $app->getContainer();

// .env file vars
$env_path = $container->get('settings')['ovpnscripts_path'] . DIRECTORY_SEPARATOR . "etc";
$dotenv = new \Dotenv\Dotenv($env_path);

$env_vars = ['OVPNSCRIPTS_PATH' => $container->get('settings')['ovpnscripts_path']];

foreach ($dotenv->load() as $line) {
    if (preg_match('/^\#/', $line)) {
        continue;
    }

    list($key, $value) = preg_split('/=/', $line);
    $env_vars[$key] = str_replace('"', '', $value);
}

$container['env_vars'] = $env_vars;

// set timezone in session
$_SESSION['timezone'] = $container->get('settings')['timezone'];

// Twig
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../views/twig/templates', [
        'cache' => '../views/twig/cache',
        'auto_reload' => true,
    ]);

    // Instantiate and add Slim specific extension
    $base_path = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $base_path));

    // glabal variables    
    $view->getEnvironment()->addGlobal('session', $_SESSION);
    $view->getEnvironment()->addGlobal('menu', $container->get('settings')['menu']);

    return $view;
};

// Flash messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// OpenVPN
$container['ovpnapi'] = function ($container) {
    return new OpenVPN\API("localhost", "7505", $container->get('settings')['ovpnscripts_path']);
};

// Cert Repository
$container['cert_repository'] = function ($container) {
    try {
        $db_path = $container->get('settings')['db_file'];
        $sqlite_connector = new \OpenVPN\DB\SQLiteConnector($db_path);
        return new \OpenVPN\DB\CertRepository($sqlite_connector->connect());
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }
};

// Auth Controller
$container['AuthController'] = function ($container) {
    $passwd_filename = $container->get('settings')['passwd_filename'];
    $realm = $container->get('settings')['digest_realm'];

    $auth_service = new App\Services\AuthService($passwd_filename, $realm);

    return new App\Controllers\AuthController($container->view, $container->flash, $auth_service);
};

// Server Controller
$container['ServerController'] = function ($container) {
    return new App\Controllers\ServerController($container->view, $container->flash, $container->ovpnapi, $container->cert_repository, $container['env_vars']);
};

// Connections Controller
$container['ConnectionsController'] = function ($container) {
    return new App\Controllers\ConnectionsController($container->view, $container->flash, $container->ovpnapi, $container->cert_repository);
};

// Clients Controller
$container['ClientsController'] = function ($container) {
    return new App\Controllers\ClientsController($container->view, $container->flash, $container->ovpnapi, $container->cert_repository);
};

// Settings Controller
$container['SettingsController'] = function ($container) use ($env_vars) {
    return new App\Controllers\SettingsController($container->view, $env_vars);
};

// Database Controller
$container['DatabaseController'] = function ($container) {
    return new App\Controllers\DatabaseController($container->view, $container->flash, $container->ovpnapi, $container->cert_repository);
};
