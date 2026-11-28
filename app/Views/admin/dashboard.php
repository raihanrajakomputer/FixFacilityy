<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="top">
    <div>
        <h1>Dashboard Admin</h1>
        <p class="lead">Ringkasan seluruh laporan kerusakan fasilitas kampus FixFacility</p>
    </div>
    <a href="<?= site_url('admin/laporan') ?>" class="btn">Kelola Laporan</a>
</div>

<div class="grid4">
    <div class="stat">
        <span class="stat-label">Total Laporan</span>
        <span class="stat-val"><?= esc((string) $totalLaporan) ?></span>
        <span class="stat-sub">Seluruh laporan masuk</span>
    </div>
    <div class="stat">
        <span class="stat-label">Menunggu</span>
        <span class="stat-val" style="color: var(--wtx);"><?= esc((string) ($statusCounts['Menunggu'] ?? 0)) ?></span>
        <span class="stat-sub">Belum ditindaklanjuti</span>
    </div>
    <div class="stat">
        <span class="stat-label">Diproses</span>
        <span class="stat-val" style="color: var(--ptx);"><?= esc((string) ($statusCounts['Diproses'] ?? 0)) ?></span>
        <span class="stat-sub">Sedang dalam penanganan</span>
    </div>
    <div class="stat">
        <span class="stat-label">Selesai</span>
        <span class="stat-val" style="color: var(--oktx);"><?= esc((string) ($statusCounts['Selesai'] ?? 0)) ?></span>
        <span class="stat-sub">Telah selesai diperbaiki</span>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; flex-wrap: wrap;">
        <h2>Ringkasan Status Laporan</h2>
        <a href="<?= site_url('admin/rekap') ?>" style="font-size: 13px;">Lihat Rekap Bulanan &rarr;</a>
    </div>

    <?php foreach ($breakdown as $item): ?>
        <div class="bar">
            <i><?= esc($item['nama']) ?></i>
            <div class="bar-track">
                <u style="width: <?= esc((string) $item['persentase'], 'attr') ?>%; background: <?= esc($item['color'], 'attr') ?>;"></u>
            </div>
            <b style="width: auto; min-width: 78px;"><?= esc((string) $item['count']) ?> (<?= esc((string) $item['persentase']) ?>%)</b>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;">
        <h2>Laporan Terbaru</h2>
        <a href="<?= site_url('admin/laporan') ?>" style="font-size: 13px;">Lihat Semua &rarr;</a>
    </div>

    <?php if (empty($recentLaporan)): ?>
        <?= view('partials/empty_state', [
            'title'   => 'Belum ada laporan masuk',
            'message' => 'Laporan kerusakan fasilitas yang dikirim oleh pelapor akan tampil di sini.',
        ]) ?>
    <?php else: ?>
        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Judul &amp; Lokasi</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLaporan as $row): ?>
                        <tr>
                            <td><strong><?= esc($row['kode']) ?></strong></td>
                            <td>
                                <div style="font-weight: 600; color: var(--tx);"><?= esc($row['judul']) ?></div>
                                <span class="muted" style="font-size: 12px;"><?= esc($row['fasilitas_nama'] ?? '-') ?> &bull; <?= esc($row['lokasi']) ?></span>
                            </td>
                            <td>
                                <div><?= esc($row['pelapor_nama'] ?? '-') ?></div>
                                <?php if (! empty($row['pelapor_nim'])): ?>
                                    <span class="muted" style="font-size: 12px;"><?= esc($row['pelapor_nim']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($row['kategori_nama'] ?? '-') ?></td>
                            <td><?= view('partials/status_badge', ['status' => $row['status_nama'] ?? 'Menunggu']) ?></td>
                            <td class="muted" style="white-space: nowrap;">
                                <?= esc(! empty($row['created_at']) ? date('d M Y', strtotime((string) $row['created_at'])) : '-') ?>
                            </td>
                            <td>
                                <a href="<?= site_url('admin/laporan/' . rawurlencode((string) $row['kode'])) ?>" class="btn btn-sm btn-outline">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
