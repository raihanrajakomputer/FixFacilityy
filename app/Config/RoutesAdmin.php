<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Dikelola oleh Developer 4 (Modul Admin)

$routes->group('admin', ['filter' => ['auth', 'role:admin']], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('laporan', 'AdminController::laporanIndex');
    $routes->get('laporan/(:segment)', 'AdminController::laporanShow/$1');
    $routes->post('laporan/(:segment)/status', 'AdminController::laporanUpdate/$1');
    $routes->delete('laporan/(:segment)', 'AdminController::laporanDestroy/$1');
    $routes->get('rekap', 'AdminController::rekap');
});
