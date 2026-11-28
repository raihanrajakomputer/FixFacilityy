<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Dikelola oleh Developer 3 (Modul Pelapor)
$routes->group('pelapor', ['filter' => ['auth', 'role:pelapor']], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'PelaporController::dashboard');
    $routes->get('laporan', 'PelaporController::index');
    $routes->get('laporan/buat', 'PelaporController::create');
    $routes->post('laporan', 'PelaporController::store');
    $routes->get('laporan/(:segment)', 'PelaporController::show/$1');
});
