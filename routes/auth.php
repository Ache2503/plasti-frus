<?php

/** @var App\Core\Router $router */

$router->get('/login', 'Auth\AuthController@showLogin');
$router->post('/login', 'Auth\AuthController@login');
$router->get('/register', 'Auth\AuthController@showRegister');
$router->post('/register', 'Auth\AuthController@register');
$router->get('/logout', 'Auth\AuthController@logout');
$router->get('/acceso-denegado', 'Auth\AuthController@accesoDenegado');
