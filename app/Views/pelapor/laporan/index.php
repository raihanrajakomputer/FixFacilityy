<?= $this->extend('layouts/pelapor') ?>

<?= $this->section('content') ?>
<div class="top">
    <div>
        <h1>Laporan Saya</h1>
        <p class="lead">Daftar seluruh laporan kerusakan fasilitas yang pernah Anda kirimkan.</p>
    </div>
    <a href="<?= site_url('pelapor/laporan/buat') ?>" class="btn">+ Buat Laporan</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<section class="card">
    <form action="<?= site_url('pelapor/laporan') ?>" method="get" class="fi">
        <div class="fi-search">
            <label for="filter-q">Cari Laporan</label>
            <input
                type="text"
                id="filter-q"
                name="q"
                value="<?= esc($filters['q'] ?? '') ?>"
                placeholder="Cari kode, judul, lokasi, atau fasilitas..."
            >
        </div>

        <div class="fi-status">
            <label for="filter-status">Status</label>
            <select id="filter-status" name="status" class="select">
                <option value="">Semua Status</option>
                <?php foreach ($statusList ?? [] as $status): ?>
                    <option value="<?= esc((string) $status['id']) ?>" <?= (string) ($filters['status'] ?? '') === (string) $status['id'] ? 'selected' : '' ?>>
                        <?= esc($status['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="fi-kategori">
            <label for="filter-kategori">Kategori</label>
            <select id="filter-kategori" name="kategori" class="select">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategoriList ?? [] as $kategori): ?>
                    <option value="<?= esc((string) $kategori['id']) ?>" <?= (string) ($filters['kategori'] ?? '') === (string) $kategori['id'] ? 'selected' : '' ?>>
                        <?= esc($kategori['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="fi-btn">
            <button type="submit" class="btn">Filter</button>
        </div>
    </form>
</section>

<section class="card">
    <?php if (empty($laporanList)): ?>
        <?php if (! empty($hasFilter)): ?>
            <?= view('partials/empty_state', [
                'title'      => 'Laporan tidak ditemukan',
                'message'    => 'Tidak ada laporan yang cocok dengan kata kunci atau filter yang Anda pilih.',
                'actionUrl'  => site_url('pelapor/laporan'),
                'actionText' => 'Reset Filter',
            ]) ?>
        <?php else: ?>
            <?= view('partials/empty_state', [
                'title'      => 'Belum ada laporan',
                'message'    => 'Anda belum pernah mengirimkan laporan kerusakan fasilitas. Silakan buat laporan baru untuk mulai melaporkan.',
                'actionUrl'  => site_url('pelapor/laporan/buat'),
                'actionText' => 'Buat Laporan',
            ]) ?>
        <?php endif; ?>
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
                    <?php foreach ($laporanList as $item): ?>
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
