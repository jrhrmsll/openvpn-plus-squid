<?php

return [
    'settings' => [
        // set to false in production
        'displayErrorDetails' => true,
        // Allow the web server to send the content-length header
        'addContentLengthHeader' => false,
        'determineRouteBeforeAppMiddleware' => true,
        // Twig settings
        'renderer' => [
            'template_path' => '../views/',
            'base_path' => '/ovpnphp/'
        ],
        // ovpnscripts path
        'ovpnscripts_path' => '/opt/ovpnscripts/',
        // Sqlite Database path
        'db_file' => '../db/ovpn.db',
        // Auth
        'passwd_filename' => '../db/passwd.digest',
        'digest_realm' => 'ovpnphp',
        // Menu
        'menu' => [
            'connections' => 'Connections',
            'clients' => 'Clients',
            'settings' => 'Settings',
            'tools' => 'Tools',
        ],
        // timezone default is UTC
        'timezone' => 'Europe/Madrid'
    ],
];
