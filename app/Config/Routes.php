<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', static function () {
    $session = session();

    if ($session->get('logged_in') === true) {
        $role = $session->get('user_role');

        if ($role === 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        if ($role === 'pelapor') {
            return redirect()->to('/pelapor/dashboard');
        }
    }

    return redirect()->to('/login');
});

require APPPATH . 'Config/RoutesAuth.php';
require APPPATH . 'Config/RoutesPelapor.php';
require APPPATH . 'Config/RoutesAdmin.php';
