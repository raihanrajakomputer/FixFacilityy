<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * Tampilkan halaman login atau arahkan ke dashboard jika sudah masuk.
     */
    public function showLoginForm(): RedirectResponse|string
    {
        $session = session();

        if ($session->get('logged_in') === true) {
            $role = (string) $session->get('user_role');

            if ($role === 'admin' || $role === 'pelapor') {
                return $this->redirectByRole($role);
            }
        }

        return view('auth/login');
    }

    /**
     * Proses autentikasi pengguna berdasarkan NIM/NIP dan kata sandi.
     */
    public function login(): RedirectResponse
    {
        $rules = [
            'nim'      => 'required',
            'password' => 'required',
        ];

        $messages = [
            'nim' => [
                'required' => 'NIM / NIP wajib diisi.',
            ],
            'password' => [
                'required' => 'Kata sandi wajib diisi.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to('/login')->withInput();
        }

        $nim      = trim((string) $this->request->getPost('nim'));
        $password = (string) $this->request->getPost('password');

        if ($nim === '') {
            return redirect()->to('/login')
                ->withInput()
                ->with('error', 'NIM / NIP wajib diisi.');
        }

        $userModel = new UserModel();
        $user      = $userModel->findByNimNip($nim);

        if ($user === null || ! password_verify($password, (string) $user['password'])) {
            return redirect()->to('/login')
                ->withInput()
                ->with('error', 'NIM / NIP atau kata sandi salah.');
        }

        $role = (string) ($user['role'] ?? '');
        if (! in_array($role, ['admin', 'pelapor'], true)) {
            return redirect()->to('/login')
                ->withInput()
                ->with('error', 'Peran akun tidak dikenali.');
        }

        $session = session();
        if (session_status() === PHP_SESSION_ACTIVE) {
            $session->regenerate(true);
        }
        $session->set([
            'user_id'   => (int) $user['id'],
            'user_name' => (string) $user['name'],
            'user_nim'  => (string) $user['nim_nip'],
            'user_role' => $role,
            'logged_in' => true,
        ]);

        return $this->redirectByRole($role);
    }

    /**
     * Hancurkan sesi pengguna dan arahkan kembali ke halaman login.
     */
    public function logout(): RedirectResponse
    {
        $session = session();
        $session->remove(['user_id', 'user_name', 'user_nim', 'user_role', 'logged_in']);
        $session->destroy();

        return redirect()->to('/login');
    }

    /**
     * Arahkan pengguna ke dashboard sesuai perannya.
     */
    private function redirectByRole(string $role): RedirectResponse
    {
        return match ($role) {
            'admin'   => redirect()->to('/admin/dashboard'),
            'pelapor' => redirect()->to('/pelapor/dashboard'),
            default   => redirect()->to('/login'),
        };
    }
}
