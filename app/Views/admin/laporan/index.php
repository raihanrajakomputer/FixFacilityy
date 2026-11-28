<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    .pager-wrap {
        margin-top: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .pager-info {
        color: var(--mu);
        font-size: 13px;
    }
    ul.pagination {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    ul.pagination li a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border: 1px solid var(--ln);
        border-radius: 8px;
        background: var(--card);
        color: var(--tx);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.15s, border-color 0.15s, color 0.15s;
    }
    ul.pagination li a:hover {
        border-color: var(--pr);
        color: var(--pr);
        background: var(--prs);
        text-decoration: none;
    }
    ul.pagination li.active a {
        background: var(--pr);
        border-color: var(--pr);
        color: #fff;
    }
    .table-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="top">
    <div>
        <h1>Kelola Laporan</h1>
        <p class="lead">Cari, filter, perbarui status, dan kelola seluruh laporan kerusakan fasilitas</p>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success" role="status">
        <?= esc((string) session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error" role="alert">
        <?= esc((string) session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="card">
    <form action="<?= site_url('admin/laporan') ?>" method="get" class="fi">
        <div class="fi-search">
            <label for="filter-q">Cari Laporan</label>
            <input
                type="text"
                id="filter-q"
                name="q"
                value="<?= esc($filters['q'] ?? '', 'attr') ?>"
                placeholder="Cari kode, judul, lokasi, atau nama pelapor..."
            >
        </div>

        <div class="fi-status">
            <label for="filter-status">Status</label>
            <select id="filter-status" name="status" class="select">
                <option value="">Semua Status</option>
                <?php foreach ($statuses as $st): ?>
                    <?php $isSelected = ($filters['status'] ?? '') === $st['nama'] || ($filters['status'] ?? '') === (string) $st['id']; ?>
                    <option value="<?= esc($st['nama'], 'attr') ?>"<?= $isSelected ? ' selected' : '' ?>>
                        <?= esc($st['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="fi-kategori">
            <label for="filter-kategori">Kategori</label>
            <select id="filter-kategori" name="kategori" class="select">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategoriList as $kat): ?>
                    <?php $isKatSelected = ($filters['kategori'] ?? '') === (string) $kat['id'] || strcasecmp((string) ($filters['kategori'] ?? ''), (string) $kat['nama']) === 0; ?>
                    <option value="<?= esc((string) $kat['id'], 'attr') ?>"<?= $isKatSelected ? ' selected' : '' ?>>
                        <?= esc($kat['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="fi-btn" style="display: flex; gap: 8px; align-items: center;">
            <button type="submit" class="btn">Terapkan</button>
            <?php if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '' || ($filters['kategori'] ?? '') !== ''): ?>
                <a href="<?= site_url('admin/laporan') ?>" class="btn gr">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <?php if (empty($laporanList)): ?>
        <?php
        $hasFilter = ($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '' || ($filters['kategori'] ?? '') !== '';
        ?>
        <?= view('partials/empty_state', [
            'title'      => $hasFilter ? 'Laporan tidak ditemukan' : 'Belum ada laporan',
            'message'    => $hasFilter
                ? 'Tidak ada laporan yang cocok dengan kata kunci atau filter yang Anda pilih.'
                : 'Belum ada laporan kerusakan fasilitas yang dikirim oleh pelapor.',
            'actionUrl'  => $hasFilter ? site_url('admin/laporan') : null,
            'actionText' => $hasFilter ? 'Reset Filter' : null,
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
                    <?php foreach ($laporanList as $row): ?>
                        <?php
                        $kode       = (string) $row['kode'];
                        $judul      = (string) $row['judul'];
                        $deleteUrl  = site_url('admin/laporan/' . rawurlencode($kode));
                        ?>
                        <tr>
                            <td><strong><?= esc($kode) ?></strong></td>
                            <td>
                                <div style="font-weight: 600; color: var(--tx);"><?= esc($judul) ?></div>
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
                                <div class="table-actions">
                                    <a href="<?= site_url('admin/laporan/' . rawurlencode($kode)) ?>" class="btn btn-sm btn-outline">Detail</a>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger-outline"
                                        onclick="bukaModalHapus(<?= esc(json_encode($kode), 'attr') ?>, <?= esc(json_encode($judul), 'attr') ?>, <?= esc(json_encode($deleteUrl), 'attr') ?>)"
                                    >Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pager-wrap">
            <span class="pager-info">
                Menampilkan <?= esc((string) count($laporanList)) ?> dari <?= esc((string) $pager->getTotal()) ?> laporan
                (Halaman <?= esc((string) $pager->getCurrentPage()) ?> dari <?= esc((string) max(1, $pager->getPageCount())) ?>)
            </span>
            <?php if ($pager->getPageCount() > 1): ?>
                <?= $pager->links('default', 'default_full') ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('partials/modal_hapus') ?>
<?= $this->endSection() ?>
