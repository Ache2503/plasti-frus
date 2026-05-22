<?php

/** @var App\Core\Router $router */

$router->get('/login', 'Auth\AuthController@showLogin');
$router->post('/login', 'Auth\AuthController@login');
$router->get('/register', 'Auth\AuthController@showRegister');
$router->post('/register', 'Auth\AuthController@register');
$router->get('/logout', 'Auth\AuthController@logout');
$router->get('/acceso-denegado', 'Auth\AuthController@accesoDenegado');

$router->get('/olvide-contrasena', 'Auth\PasswordResetController@showForgotForm');
$router->post('/olvide-contrasena', 'Auth\PasswordResetController@sendResetLink');
$router->get('/restablecer-contrasena', 'Auth\PasswordResetController@showResetForm');
$router->post('/restablecer-contrasena', 'Auth\PasswordResetController@resetPassword');
