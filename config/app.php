<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Plasti Frus - Sistema de Gestión',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/fabrica_plasticos',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => (bool)($_ENV['APP_DEBUG'] ?? false),
    'timezone' => 'America/Mexico_City',
    'locale' => 'es_MX',
    'currency' => 'MXN',
    'session_time' => 3600,
    'comision_porcentaje' => 5,

    'roles' => [
        1 => 'admin',
        2 => 'operador',
        3 => 'supervisor',
        4 => 'vendedor',
        5 => 'cliente',
        6 => 'contador',
    ],

    'rol_vendedor' => 4,

    'modules' => [
        'auth', 'dashboard', 'production', 'sales', 'purchasing',
        'accounting', 'crm', 'quality', 'maintenance', 'inventory',
        'incidents', 'notifications', 'reports', 'portal', 'system',
    ],
];
