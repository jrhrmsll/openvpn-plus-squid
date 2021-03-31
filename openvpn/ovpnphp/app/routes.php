<?php

// middleware
$app->add(new RKA\Middleware\IpAddress(true, []));
$app->add(new App\Middleware\Auth());
$app->add(new App\Middleware\ActiveLink(array_keys($container->get('settings')['menu'])));

// security
$app->get('/login', 'AuthController:login')->setName('login');
$app->post('/signin', 'AuthController:signin')->setName('signin');
$app->get('/logout', 'AuthController:logout')->setName('logout');

// homepage
$app->get('/', 'ServerController:index')->setName('homepage');
$app->get('/server', 'ServerController:index')->setName('server');

// connections
$app->get('/connections', 'ConnectionsController:index')->setName('connections');
$app->get('/connections/{name}', 'ConnectionsController:show')->setName('connections_show');
$app->get('/connections/{name}/disconnet', 'ConnectionsController:delete')->setName('connections_disconnet');

// clients
$app->get('/clients', 'ClientsController:index')->setName('clients');
$app->get('/clients/create', 'ClientsController:create')->setName('clients_create');
$app->post('/clients', 'ClientsController:store')->setName('clients_store');
$app->get('/clients/{name}', 'ClientsController:show')->setName('clients_show');
$app->get('/clients/{name}/revoke', 'ClientsController:delete')->setName('clients_revoke');
$app->get('/clients/{name}/config', 'ClientsController:config')->setName('clients_config');

// settings
$app->get('/settings', 'SettingsController:index')->setName('settings');

// tools
$app->get('/tools', 'DatabaseController:index')->setName('tools');
$app->get('/tools/reset', 'DatabaseController:reset')->setName('tools_reset');

// callback endpoint
$app->post('/notification', 'DatabaseController:notification')
        ->setName('notification')
        ->add(new App\Middleware\EndPointSecurity());

