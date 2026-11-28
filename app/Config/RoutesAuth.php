<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Dikelola oleh Developer 1 (Modul Autentikasi)
$routes->get('login', 'AuthController::showLoginForm');
$routes->post('login', 'AuthController::login');
$routes->post('logout', 'AuthController::logout', ['filter' => 'auth']);
