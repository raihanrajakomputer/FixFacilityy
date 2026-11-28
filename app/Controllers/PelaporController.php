<?php

namespace App\Controllers;

use App\Models\FasilitasModel;
use App\Models\KategoriModel;
use App\Models\LaporanModel;
use App\Models\RiwayatStatusModel;
use App\Models\StatusLaporanModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class PelaporController extends BaseController
{
    protected $helpers = ['form', 'url'];

    protected LaporanModel $laporanModel;
    protected FasilitasModel $fasilitasModel;
    protected KategoriModel $kategoriModel;
    protected StatusLaporanModel $statusModel;
    protected RiwayatStatusModel $riwayatModel;

    public function __construct()
    {
        $this->laporanModel   = new LaporanModel();
        $this->fasilitasModel = new FasilitasModel();
        $this->kategoriModel  = new KategoriModel();
        $this->statusModel    = new StatusLaporanModel();
        $this->riwayatModel   = new RiwayatStatusModel();
    }

    /**
     * Ambil user_id pelapor yang sedang login dari session.
     */
    private function currentUserId(): int
    {
        return (int) session('user_id');
    }

    /**
     * Dashboard pelapor: statistik laporan milik user dan daftar laporan terbaru.
     */
    public function dashboard(): string|RedirectResponse
    {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return redirect()->to(site_url('login'));
        }

        $stats = [
            'total'    => 0,
            'menunggu' => 0,
            'diproses' => 0,
            'selesai'  => 0,
        ];

        $countsRaw = $this->laporanModel
            ->select('status_laporan.nama AS status_nama, COUNT(*) AS total')
            ->join('status_laporan', 'status_laporan.id = laporan.status_id', 'left')
            ->where('laporan.user_id', $userId)
            ->groupBy('status_laporan.nama')
            ->findAll();

        foreach ($countsRaw as $row) {
            $namaStatus = strtolower(trim((string) ($row['status_nama'] ?? '')));
            $jumlah     = (int) ($row['total'] ?? 0);

            $stats['total'] += $jumlah;
            if (array_key_exists($namaStatus, $stats)) {
                $stats[$namaStatus] = $jumlah;
            }
        }

        $laporanTerbaru = $this->laporanModel
            ->getWithRelations()
            ->where('laporan.user_id', $userId)
            ->orderBy('laporan.created_at', 'DESC')
            ->orderBy('laporan.id', 'DESC')
            ->findAll(5);

        return view('pelapor/dashboard', [
            'title'          => 'Dashboard Pelapor - FixFacility',
            'activeMenu'     => 'dashboard',
            'userName'       => (string) (session('user_name') ?? 'Pelapor'),
            'stats'          => $stats,
            'laporanTerbaru' => $laporanTerbaru,
        ]);
    }

    /**
     * Daftar seluruh laporan milik pelapor yang sedang login.
     */
    public function index(): string|RedirectResponse
    {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return redirect()->to(site_url('login'));
        }

        $keyword    = trim((string) $this->request->getGet('q'));
        $statusId   = trim((string) $this->request->getGet('status'));
        $kategoriId = trim((string) $this->request->getGet('kategori'));

        $query = $this->laporanModel
            ->getWithRelations()
            ->where('laporan.user_id', $userId);

        if ($keyword !== '') {
            $query->groupStart()
                ->like('laporan.kode', $keyword)
                ->orLike('laporan.judul', $keyword)
                ->orLike('laporan.lokasi', $keyword)
                ->orLike('fasilitas.nama', $keyword)
                ->groupEnd();
        }

        if ($statusId !== '' && ctype_digit($statusId)) {
            $query->where('laporan.status_id', (int) $statusId);
        }

        if ($kategoriId !== '' && ctype_digit($kategoriId)) {
            $query->where('laporan.kategori_id', (int) $kategoriId);
        }

        $laporanList = $query
            ->orderBy('laporan.created_at', 'DESC')
            ->orderBy('laporan.id', 'DESC')
            ->findAll();

        $hasFilter = $keyword !== '' || $statusId !== '' || $kategoriId !== '';

        return view('pelapor/laporan/index', [
            'title'        => 'Laporan Saya - FixFacility',
            'activeMenu'   => 'laporan',
            'laporanList'  => $laporanList,
            'statusList'   => $this->statusModel->getOrdered(),
            'kategoriList' => $this->kategoriModel->orderBy('nama', 'ASC')->findAll(),
            'hasFilter'    => $hasFilter,
            'filters'      => [
                'q'        => $keyword,
                'status'   => $statusId,
                'kategori' => $kategoriId,
            ],
        ]);
    }

    /**
     * Form buat laporan baru dengan data fasilitas dan kategori dari database.
     */
    public function create(): string|RedirectResponse
    {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return redirect()->to(site_url('login'));
        }

        return view('pelapor/laporan/create', [
            'title'         => 'Buat Laporan - FixFacility',
            'activeMenu'    => 'buat',
            'fasilitasList' => $this->fasilitasModel->orderBy('nama', 'ASC')->findAll(),
            'kategoriList'  => $this->kategoriModel->orderBy('nama', 'ASC')->findAll(),
        ]);
    }

    /**
     * Validasi dan simpan laporan baru + riwayat status awal dalam transaksi database.
     */
    public function store(): RedirectResponse
    {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return redirect()->to(site_url('login'));
        }

        $rules = [
            'judul' => [
                'label'  => 'Judul laporan',
                'rules'  => 'required|max_length[200]',
                'errors' => [
                    'required'   => 'Judul laporan wajib diisi.',
                    'max_length' => 'Judul laporan maksimal 200 karakter.',
                ],
            ],
            'fasilitas_id' => [
                'label'  => 'Fasilitas',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[fasilitas.id]',
                'errors' => [
                    'required'           => 'Fasilitas wajib dipilih.',
                    'is_natural_no_zero' => 'Pilihan fasilitas tidak valid.',
                    'is_not_unique'      => 'Fasilitas yang dipilih tidak tersedia.',
                ],
            ],
            'lokasi' => [
                'label'  => 'Lokasi',
                'rules'  => 'required|max_length[200]',
                'errors' => [
                    'required'   => 'Lokasi kerusakan wajib diisi.',
                    'max_length' => 'Lokasi kerusakan maksimal 200 karakter.',
                ],
            ],
            'kategori_id' => [
                'label'  => 'Kategori',
                'rules'  => 'required|is_natural_no_zero|is_not_unique[kategori.id]',
                'errors' => [
                    'required'           => 'Kategori wajib dipilih.',
                    'is_natural_no_zero' => 'Pilihan kategori tidak valid.',
                    'is_not_unique'      => 'Kategori yang dipilih tidak tersedia.',
                ],
            ],
            'deskripsi' => [
                'label'  => 'Deskripsi',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Deskripsi kerusakan wajib diisi.',
                ],
            ],
            'foto' => [
                'label'  => 'Foto bukti',
                'rules'  => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|ext_in[foto,jpg,jpeg,png,webp]|max_size[foto,5120]',
                'errors' => [
                    'uploaded' => 'Foto bukti kerusakan wajib diunggah.',
                    'is_image' => 'File yang diunggah harus berupa gambar.',
                    'mime_in'  => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
                    'ext_in'   => 'Ekstensi foto harus berupa jpg, jpeg, png, atau webp.',
                    'max_size' => 'Ukuran foto maksimal 5 MB (5120 KB).',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $statusMenunggu = $this->statusModel->findByNama('Menunggu');
        if (! $statusMenunggu) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Status awal "Menunggu" belum tersedia di database.');
        }

        $foto = $this->request->getFile('foto');
        if ($foto === null || ! $foto->isValid() || $foto->hasMoved()) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['foto' => 'Gagal memproses file foto yang diunggah.']);
        }

        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'laporan';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $namaFoto = $foto->getRandomName();
        $foto->move($uploadDir, $namaFoto);

        $db = $this->laporanModel->db;
        $db->transBegin();

        try {
            $kode = $this->laporanModel->generateNextKode();

            $laporanId = $this->laporanModel->insert([
                'kode'         => $kode,
                'user_id'      => $userId,
                'fasilitas_id' => (int) $this->request->getPost('fasilitas_id'),
                'kategori_id'  => (int) $this->request->getPost('kategori_id'),
                'status_id'    => (int) $statusMenunggu['id'],
                'judul'        => trim((string) $this->request->getPost('judul')),
                'lokasi'       => trim((string) $this->request->getPost('lokasi')),
                'deskripsi'    => trim((string) $this->request->getPost('deskripsi')),
                'foto'         => $namaFoto,
            ], true);

            if (! $laporanId) {
                throw new \RuntimeException('Gagal menyimpan data laporan.');
            }

            $riwayatId = $this->riwayatModel->insert([
                'laporan_id'  => (int) $laporanId,
                'status_id'   => (int) $statusMenunggu['id'],
                'diubah_oleh' => $userId,
                'keterangan'  => 'Laporan baru dibuat oleh pelapor.',
            ], true);

            if (! $riwayatId) {
                throw new \RuntimeException('Gagal mencatat riwayat status awal.');
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal diselesaikan.');
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            $savedFile = $uploadDir . DIRECTORY_SEPARATOR . $namaFoto;
            if (is_file($savedFile)) {
                @unlink($savedFile);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('pelapor/laporan/' . $kode))
            ->with('success', 'Laporan ' . $kode . ' berhasil dikirim dan sedang menunggu peninjauan.');
    }

    /**
     * Detail satu laporan milik pelapor beserta foto bukti dan riwayat status.
     */
    public function show(string $kode): string|ResponseInterface|RedirectResponse
    {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return redirect()->to(site_url('login'));
        }

        $laporan = $this->laporanModel->findByKodeWithRelations($kode);

        if (! $laporan) {
            throw PageNotFoundException::forPageNotFound('Laporan dengan kode "' . $kode . '" tidak ditemukan.');
        }

        if ((int) $laporan['user_id'] !== $userId) {
            return $this->response
                ->setStatusCode(403)
                ->setBody(
                    '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>403 Akses Ditolak</title></head>'
                    . '<body style="font-family:Inter,sans-serif;padding:40px;text-align:center;color:#1e293b;">'
                    . '<h1>403 - Akses Ditolak</h1>'
                    . '<p>Anda tidak memiliki izin untuk melihat laporan ini.</p>'
                    . '<p><a href="' . esc(site_url('pelapor/laporan'), 'attr') . '">Kembali ke Laporan Saya</a></p>'
                    . '</body></html>'
                );
        }

        $riwayat = $this->riwayatModel->getByLaporanId((int) $laporan['id']);

        return view('pelapor/laporan/show', [
            'title'      => 'Detail Laporan ' . $laporan['kode'] . ' - FixFacility',
            'activeMenu' => 'laporan',
            'laporan'    => $laporan,
            'riwayat'    => $riwayat,
        ]);
    }
}
