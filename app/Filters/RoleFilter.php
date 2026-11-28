<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Pastikan peran pengguna pada session sesuai dengan parameter filter route.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if ($session->get('logged_in') !== true) {
            return redirect()->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $userRole     = (string) $session->get('user_role');
        $allowedRoles = is_array($arguments) ? $arguments : [];

        if ($allowedRoles !== [] && ! in_array($userRole, $allowedRoles, true)) {
            $dashboardUrl = match ($userRole) {
                'admin'   => site_url('admin/dashboard'),
                'pelapor' => site_url('pelapor/dashboard'),
                default   => site_url('login'),
            };

            $html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
                . '<title>403 - Akses Ditolak</title>'
                . '<link rel="stylesheet" href="' . esc(base_url('css/app.css')) . '">'
                . '</head><body><div class="lr" style="min-height:100vh;">'
                . '<div class="card" style="max-width:460px;width:100%;text-align:center;">'
                . '<h1 style="margin-bottom:8px;">403 - Akses Ditolak</h1>'
                . '<p class="lead" style="margin-bottom:20px;">Anda tidak memiliki izin untuk mengakses halaman ini.</p>'
                . '<a href="' . esc($dashboardUrl) . '" class="btn w">Kembali ke Dashboard</a>'
                . '</div></div></body></html>';

            return service('response')
                ->setStatusCode(403)
                ->setBody($html);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi tambahan setelah request.
    }
}
