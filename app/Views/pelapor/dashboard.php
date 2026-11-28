<?= $this->extend('layouts/pelapor') ?>

<?= $this->section('content') ?>
<div class="top">
    <div>
        <h1>Dashboard Pelapor</h1>
        <p class="lead">Selamat datang, <?= esc($userName ?? 'Pelapor') ?>. Pantau ringkasan laporan kerusakan fasilitas Anda.</p>
    </div>
    <a href="<?= site_url('pelapor/laporan/buat') ?>" class="btn">+ Buat Laporan</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<section class="grid4" aria-label="Statistik Laporan">
    <div class="stat">
        <span class="stat-label">Total Laporan</span>
        <strong class="stat-val"><?= esc((string) ($stats['total'] ?? 0)) ?></strong>
        <span class="stat-sub">Semua laporan Anda</span>
    </div>
    <div class="stat">
        <span class="stat-label">Menunggu</span>
        <strong class="stat-val"><?= esc((string) ($stats['menunggu'] ?? 0)) ?></strong>
        <span class="stat-sub">Menunggu verifikasi</span>
    </div>
    <div class="stat">
        <span class="stat-label">Diproses</span>
        <strong class="stat-val"><?= esc((string) ($stats['diproses'] ?? 0)) ?></strong>
        <span class="stat-sub">Sedang ditangani</span>
    </div>
    <div class="stat">
        <span class="stat-label">Selesai</span>
        <strong class="stat-val"><?= esc((string) ($stats['selesai'] ?? 0)) ?></strong>
        <span class="stat-sub">Telah diselesaikan</span>
    </div>
</section>

<section class="card">
    <div class="top">
        <div>
            <h2>Laporan Terbaru</h2>
            <p class="lead">Lima laporan terakhir yang Anda kirimkan.</p>
        </div>
        <a href="<?= site_url('pelapor/laporan') ?>" class="btn btn-sm btn-outline">Lihat Semua</a>
    </div>

    <?php if (empty($laporanTerbaru)): ?>
        <?= view('partials/empty_state', [
            'title'      => 'Belum ada laporan',
            'message'    => 'Anda belum pernah membuat laporan kerusakan fasilitas. Mulai buat laporan pertama Anda sekarang.',
            'actionUrl'  => site_url('pelapor/laporan/buat'),
            'actionText' => 'Buat Laporan',
        ]) ?>
    <?php else: ?>
        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Judul &amp; Fasilitas</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporanTerbaru as $item): ?>
                        <tr>
                            <td><strong><?= esc($item['kode']) ?></strong></td>
                            <td>
                                <div><strong><?= esc($item['judul']) ?></strong></div>
                                <span class="muted"><?= esc($item['fasilitas_nama'] ?? '-') ?> &bull; <?= esc($item['kategori_nama'] ?? '-') ?></span>
                            </td>
                            <td><?= esc($item['lokasi']) ?></td>
                            <td><?= view('partials/status_badge', ['status' => $item['status_nama'] ?? 'Menunggu']) ?></td>
                            <td class="muted">
                                <?= ! empty($item['created_at']) ? esc(date('d M Y, H:i', strtotime((string) $item['created_at']))) : '-' ?>
                            </td>
                            <td>
                                <a href="<?= site_url('pelapor/laporan/' . $item['kode']) ?>" class="btn btn-sm btn-outline">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
