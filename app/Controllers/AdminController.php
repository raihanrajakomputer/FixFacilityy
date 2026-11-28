<?php

namespace App\Controllers;

use App\Models\KategoriModel;
use App\Models\LaporanModel;
use App\Models\RiwayatStatusModel;
use App\Models\StatusLaporanModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Database;
use DateTimeImmutable;
use Throwable;

class AdminController extends BaseController
{
    private const BULAN_ID = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    protected LaporanModel $laporanModel;
    protected StatusLaporanModel $statusModel;
    protected RiwayatStatusModel $riwayatModel;
    protected KategoriModel $kategoriModel;

    public function __construct()
    {
        $this->laporanModel  = new LaporanModel();
        $this->statusModel   = new StatusLaporanModel();
        $this->riwayatModel  = new RiwayatStatusModel();
        $this->kategoriModel = new KategoriModel();
    }

    /**
     * Halaman utama Dashboard Admin: total laporan, rincian per status,
     * persentase breakdown, dan daftar laporan terbaru.
     */
    public function dashboard(): string
    {
        $statuses      = $this->statusModel->getOrdered();
        $statusCounts  = $this->getStatusCounts();
        $totalLaporan  = (int) (new LaporanModel())->countAllResults();
        $breakdown     = $this->buildStatusBreakdown($statuses, $statusCounts, $totalLaporan);
        $recentLaporan = (new LaporanModel())
            ->getWithRelations()
            ->orderBy('laporan.created_at', 'DESC')
            ->orderBy('laporan.id', 'DESC')
            ->findAll(5);

        return view('admin/dashboard', [
            'title'         => 'Dashboard Admin - FixFacility',
            'activeMenu'    => 'dashboard',
            'totalLaporan'  => $totalLaporan,
            'statusCounts'  => $statusCounts,
            'breakdown'     => $breakdown,
            'recentLaporan' => $recentLaporan,
        ]);
    }

    /**
     * Daftar seluruh laporan dengan pencarian q, filter status, filter kategori,
     * dan pagination CI4 yang mempertahankan query string URL.
     */
    public function laporanIndex(): string
    {
        $q        = trim((string) $this->request->getGet('q'));
        $status   = trim((string) $this->request->getGet('status'));
        $kategori = trim((string) $this->request->getGet('kategori'));

        $laporanModel = new LaporanModel();
        $query        = $laporanModel->getWithRelations();

        if ($q !== '') {
            $query->groupStart()
                ->like('laporan.kode', $q)
                ->orLike('laporan.judul', $q)
                ->orLike('laporan.lokasi', $q)
                ->orLike('users.name', $q)
                ->groupEnd();
        }

        if ($status !== '') {
            if (ctype_digit($status)) {
                $query->where('laporan.status_id', (int) $status);
            } else {
                $query->where('status_laporan.nama', $status);
            }
        }

        if ($kategori !== '') {
            if (ctype_digit($kategori)) {
                $query->where('laporan.kategori_id', (int) $kategori);
            } else {
                $query->where('kategori.nama', $kategori);
            }
        }

        $query->orderBy('laporan.created_at', 'DESC')
            ->orderBy('laporan.id', 'DESC');

        $laporanList = $query->paginate(10, 'default');
        $pager       = $laporanModel->pager;
        $pager->only(['q', 'status', 'kategori']);

        return view('admin/laporan/index', [
            'title'        => 'Kelola Laporan - FixFacility',
            'activeMenu'   => 'laporan',
            'laporanList'  => $laporanList,
            'pager'        => $pager,
            'statuses'     => $this->statusModel->getOrdered(),
            'kategoriList' => $this->kategoriModel->orderBy('nama', 'ASC')->findAll(),
            'filters'      => [
                'q'        => $q,
                'status'   => $status,
                'kategori' => $kategori,
            ],
        ]);
    }

    /**
     * Detail lengkap satu laporan beserta foto bukti, opsi status, dan timeline riwayat.
     */
    public function laporanShow(string $kode): string
    {
        $laporan = $this->laporanModel->findByKodeWithRelations($kode);

        if ($laporan === null) {
            throw PageNotFoundException::forPageNotFound('Laporan tidak ditemukan.');
        }

        $statuses = $this->statusModel->getOrdered();
        $riwayat  = $this->riwayatModel->getByLaporanId((int) $laporan['id']);

        return view('admin/laporan/show', [
            'title'      => 'Detail Laporan ' . $laporan['kode'] . ' - FixFacility',
            'activeMenu' => 'laporan',
            'laporan'    => $laporan,
            'statuses'   => $statuses,
            'riwayat'    => $riwayat,
        ]);
    }

    /**
     * Memperbarui status laporan dan mencatat riwayat perubahan dalam transaksi database.
     */
    public function laporanUpdate(string $kode): RedirectResponse
    {
        $laporan = (new LaporanModel())->where('kode', $kode)->first();

        if ($laporan === null) {
            throw PageNotFoundException::forPageNotFound('Laporan tidak ditemukan.');
        }

        $rawStatus  = trim((string) ($this->request->getPost('status') ?? $this->request->getPost('status_id') ?? ''));
        $keterangan = trim((string) $this->request->getPost('keterangan'));

        if ($rawStatus === '') {
            return redirect()->back()->withInput()->with('error', 'Status laporan wajib dipilih.');
        }

        $targetStatus = ctype_digit($rawStatus)
            ? $this->statusModel->find((int) $rawStatus)
            : $this->statusModel->findByNama($rawStatus);

        if ($targetStatus === null) {
            return redirect()->back()->withInput()->with('error', 'Status laporan yang dipilih tidak valid.');
        }

        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Sesi admin tidak valid. Silakan login ulang.');
        }

        if ($keterangan === '') {
            $keterangan = 'Status laporan diperbarui menjadi ' . $targetStatus['nama'] . ' oleh Admin.';
        }

        $db = Database::connect();

        try {
            $db->transException(true)->transStart();

            (new LaporanModel())->update((int) $laporan['id'], [
                'status_id' => (int) $targetStatus['id'],
            ]);

            (new RiwayatStatusModel())->insert([
                'laporan_id'  => (int) $laporan['id'],
                'status_id'   => (int) $targetStatus['id'],
                'diubah_oleh' => $userId,
                'keterangan'  => $keterangan,
            ]);

            $db->transComplete();
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui status laporan.');
        }

        return redirect()
            ->to('/admin/laporan/' . rawurlencode($kode))
            ->with('success', 'Status laporan ' . $kode . ' berhasil diperbarui menjadi ' . $targetStatus['nama'] . '.');
    }

    /**
     * Menghapus laporan beserta foto bukti fisiknya (riwayat ikut terhapus via cascade).
     */
    public function laporanDestroy(string $kode): RedirectResponse
    {
        $laporan = (new LaporanModel())->where('kode', $kode)->first();

        if ($laporan === null) {
            throw PageNotFoundException::forPageNotFound('Laporan tidak ditemukan.');
        }

        $foto      = trim((string) ($laporan['foto'] ?? ''));
        $laporanId = (int) $laporan['id'];
        $db        = Database::connect();

        try {
            $db->transException(true)->transStart();

            (new RiwayatStatusModel())->where('laporan_id', $laporanId)->delete();
            (new LaporanModel())->delete($laporanId);

            $db->transComplete();
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus laporan.');
        }

        if ($foto !== '' && ! str_contains($foto, '..') && ! str_starts_with($foto, '/')) {
            $fotoPath = FCPATH . 'uploads/laporan/' . $foto;
            if (is_file($fotoPath)) {
                @unlink($fotoPath);
            }
        }

        return redirect()
            ->to('/admin/laporan')
            ->with('success', 'Laporan ' . $kode . ' berhasil dihapus.');
    }

    /**
     * Rekap laporan berdasarkan periode bulanan (6 bulan terakhir).
     * Menggunakan rentang tanggal YYYY-MM-01 00:00:00 s.d. YYYY-MM-t 23:59:59
     * agar kompatibel penuh dengan SQLite dan MySQL.
     */
    public function rekap(): string
    {
        $periodeOptions  = $this->buildPeriodeOptions();
        $requested       = trim((string) $this->request->getGet('periode'));
        $selectedPeriode = $this->resolveSelectedPeriode($requested, $periodeOptions);
        $periodeMeta     = $periodeOptions[$selectedPeriode];

        $startDate = $periodeMeta['start'];
        $endDate   = $periodeMeta['end'];

        $totalLaporan = (int) (new LaporanModel())
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->countAllResults();

        $statuses     = $this->statusModel->getOrdered();
        $statusCounts = $this->getStatusCounts($startDate, $endDate);
        $breakdown    = $this->buildStatusBreakdown($statuses, $statusCounts, $totalLaporan);

        $kategoriBreakdown = $this->getKategoriBreakdown($startDate, $endDate, $totalLaporan);

        return view('admin/rekap', [
            'title'             => 'Rekap Laporan - FixFacility',
            'activeMenu'        => 'rekap',
            'periodeOptions'    => array_values($periodeOptions),
            'selectedPeriode'   => $selectedPeriode,
            'selectedLabel'     => $periodeMeta['label'],
            'totalLaporan'      => $totalLaporan,
            'statusCounts'      => $statusCounts,
            'breakdown'         => $breakdown,
            'kategoriBreakdown' => $kategoriBreakdown,
        ]);
    }

    /**
     * Menghitung jumlah laporan per nama status, opsional dibatasi rentang tanggal.
     *
     * @return array<string, int>
     */
    private function getStatusCounts(?string $startDate = null, ?string $endDate = null): array
    {
        $counts = [];
        foreach ($this->statusModel->getOrdered() as $status) {
            $counts[$status['nama']] = 0;
        }

        $builder = (new LaporanModel())
            ->select('status_laporan.nama AS status_nama, COUNT(laporan.id) AS total')
            ->join('status_laporan', 'status_laporan.id = laporan.status_id', 'left');

        if ($startDate !== null && $endDate !== null) {
            $builder->where('laporan.created_at >=', $startDate)
                ->where('laporan.created_at <=', $endDate);
        }

        $rows = $builder->groupBy('laporan.status_id, status_laporan.nama')->findAll();

        foreach ($rows as $row) {
            $nama = (string) ($row['status_nama'] ?? '');
            if ($nama !== '') {
                $counts[$nama] = (int) $row['total'];
            }
        }

        return $counts;
    }

    /**
     * Menyusun ringkasan status beserta persentase dan warna bar.
     *
     * @param list<array<string, mixed>> $statuses
     * @param array<string, int>         $statusCounts
     *
     * @return list<array<string, mixed>>
     */
    private function buildStatusBreakdown(array $statuses, array $statusCounts, int $totalLaporan): array
    {
        $colors = [
            'Menunggu' => '#d97706',
            'Diproses' => '#2563eb',
            'Selesai'  => '#16794a',
        ];

        $breakdown = [];
        foreach ($statuses as $status) {
            $nama  = (string) $status['nama'];
            $count = $statusCounts[$nama] ?? 0;
            $pct   = $totalLaporan > 0 ? round(($count / $totalLaporan) * 100, 1) : 0.0;

            $breakdown[] = [
                'nama'       => $nama,
                'count'      => $count,
                'persentase' => $pct,
                'color'      => $colors[$nama] ?? '#2563eb',
            ];
        }

        return $breakdown;
    }

    /**
     * Menghitung rincian laporan per kategori pada periode tertentu.
     *
     * @return list<array<string, mixed>>
     */
    private function getKategoriBreakdown(string $startDate, string $endDate, int $totalLaporan): array
    {
        $kategoriList = $this->kategoriModel->orderBy('id', 'ASC')->findAll();

        $rows = (new LaporanModel())
            ->select('laporan.kategori_id, COUNT(laporan.id) AS total')
            ->where('laporan.created_at >=', $startDate)
            ->where('laporan.created_at <=', $endDate)
            ->groupBy('laporan.kategori_id')
            ->findAll();

        $countsById = [];
        foreach ($rows as $row) {
            $countsById[(int) $row['kategori_id']] = (int) $row['total'];
        }

        $result = [];
        foreach ($kategoriList as $kat) {
            $id    = (int) $kat['id'];
            $count = $countsById[$id] ?? 0;
            $pct   = $totalLaporan > 0 ? round(($count / $totalLaporan) * 100, 1) : 0.0;

            $result[] = [
                'id'         => $id,
                'nama'       => (string) $kat['nama'],
                'count'      => $count,
                'persentase' => $pct,
            ];
        }

        return $result;
    }

    /**
     * Membangun daftar 6 bulan terakhir (termasuk bulan ini) dengan format kunci 'Month Year'.
     *
     * @return array<string, array{value: string, label: string, start: string, end: string}>
     */
    private function buildPeriodeOptions(): array
    {
        $firstOfMonth = new DateTimeImmutable('first day of this month 00:00:00');
        $options      = [];

        for ($i = 0; $i < 6; $i++) {
            $dt    = $firstOfMonth->modify("-{$i} months");
            $value = $dt->format('F Y');
            $month = (int) $dt->format('n');
            $label = (self::BULAN_ID[$month] ?? $dt->format('F')) . ' ' . $dt->format('Y');

            $options[$value] = [
                'value' => $value,
                'label' => $label,
                'start' => $dt->format('Y-m-01 00:00:00'),
                'end'   => $dt->format('Y-m-t 23:59:59'),
            ];
        }

        return $options;
    }

    /**
     * Menentukan periode terpilih dari query parameter dengan fallback ke bulan berjalan.
     *
     * @param array<string, array{value: string, label: string, start: string, end: string}> $periodeOptions
     */
    private function resolveSelectedPeriode(string $requested, array $periodeOptions): string
    {
        if ($requested !== '') {
            foreach ($periodeOptions as $value => $meta) {
                if (strcasecmp($requested, $value) === 0 || strcasecmp($requested, $meta['label']) === 0) {
                    return $value;
                }
            }
        }

        return (string) array_key_first($periodeOptions);
    }
}
