<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="top">
    <div>
        <div style="margin-bottom: 10px;">
            <a href="<?= site_url('admin/laporan') ?>" class="btn gr btn-sm">&larr; Kembali ke Daftar Laporan</a>
        </div>
        <h1>Detail Laporan <?= esc($laporan['kode']) ?></h1>
        <p class="lead"><?= esc($laporan['judul']) ?></p>
    </div>
    <div>
        <?= view('partials/status_badge', ['status' => $laporan['status_nama'] ?? 'Menunggu']) ?>
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

<div class="two">
    <div>
        <div class="card">
            <h2 style="margin-bottom: 12px;">Informasi Laporan</h2>
            <div class="row">
                <span>Kode Laporan</span>
                <div><strong><?= esc($laporan['kode']) ?></strong></div>
            </div>
            <div class="row">
                <span>Judul</span>
                <div><?= esc($laporan['judul']) ?></div>
            </div>
            <div class="row">
                <span>Status Saat Ini</span>
                <div><?= view('partials/status_badge', ['status' => $laporan['status_nama'] ?? 'Menunggu']) ?></div>
            </div>
            <div class="row">
                <span>Pelapor</span>
                <div>
                    <?= esc($laporan['pelapor_nama'] ?? '-') ?>
                    <?php if (! empty($laporan['pelapor_nim'])): ?>
                        <span class="muted">(<?= esc($laporan['pelapor_nim']) ?>)</span>
                    <?php endif; ?>
                    <?php if (! empty($laporan['pelapor_email'])): ?>
                        <div class="muted" style="font-size: 12px; margin-top: 2px;"><?= esc($laporan['pelapor_email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <span>Fasilitas</span>
                <div><?= esc($laporan['fasilitas_nama'] ?? '-') ?></div>
            </div>
            <div class="row">
                <span>Kategori</span>
                <div><?= esc($laporan['kategori_nama'] ?? '-') ?></div>
            </div>
            <div class="row">
                <span>Lokasi</span>
                <div><?= esc($laporan['lokasi']) ?></div>
            </div>
            <div class="row">
                <span>Dilaporkan</span>
                <div><?= esc(! empty($laporan['created_at']) ? date('d M Y, H:i', strtotime((string) $laporan['created_at'])) : '-') ?></div>
            </div>
            <div class="row">
                <span>Deskripsi</span>
                <div style="white-space: pre-line;"><?= esc($laporan['deskripsi']) ?></div>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 14px;">Foto Bukti Kerusakan</h2>
            <?php if (! empty($laporan['foto'])): ?>
                <?php
                $fotoUrl = base_url('uploads/laporan/' . $laporan['foto']);
                $caption = $laporan['kode'] . ' — ' . $laporan['judul'];
                ?>
                <div
                    class="detail-photo-wrapper"
                    onclick="bukaLightbox(<?= esc(json_encode($fotoUrl), 'attr') ?>, <?= esc(json_encode($caption), 'attr') ?>)"
                    role="button"
                    tabindex="0"
                    onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); bukaLightbox(<?= esc(json_encode($fotoUrl), 'attr') ?>, <?= esc(json_encode($caption), 'attr') ?>); }"
                >
                    <img
                        src="<?= esc($fotoUrl, 'attr') ?>"
                        alt="Foto bukti laporan <?= esc($laporan['kode'], 'attr') ?>"
                        style="width: 100%; max-height: 360px; object-fit: cover; display: block;"
                    >
                    <div class="detail-photo-overlay">
                        <span>Klik untuk memperbesar</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="ph">Tidak ada foto bukti</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Riwayat Perkembangan</h2>
            <?php if (empty($riwayat)): ?>
                <p class="lead" style="margin-top: 12px;">Belum ada catatan riwayat perubahan status.</p>
            <?php else: ?>
                <div class="tl">
                    <?php $totalRiwayat = count($riwayat); ?>
                    <?php foreach ($riwayat as $idx => $item): ?>
                        <?php $isLatest = ($idx === $totalRiwayat - 1); ?>
                        <div class="tl-item<?= $isLatest ? ' on' : '' ?>">
                            <b><?= esc($item['status_nama'] ?? '-') ?></b>
                            <?php if (! empty($item['keterangan'])): ?>
                                <div style="margin-top: 4px; color: var(--tx); font-size: 13px;"><?= esc($item['keterangan']) ?></div>
                            <?php endif; ?>
                            <small>
                                <?= esc(! empty($item['created_at']) ? date('d M Y, H:i', strtotime((string) $item['created_at'])) : '-') ?>
                                &bull; Oleh: <?= esc($item['pengubah_nama'] ?? '-') ?>
                                (<?= esc($item['pengubah_role'] ?? '-') ?>)
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="card">
            <h2>Ubah Status Laporan</h2>
            <p class="lead" style="margin-bottom: 14px;">Pilih status cepat atau gunakan dropdown di bawah ini.</p>

            <form action="<?= site_url('admin/laporan/' . rawurlencode((string) $laporan['kode']) . '/status') ?>" method="post">
                <?= csrf_field() ?>

                <div class="chips" role="group" aria-label="Pilihan cepat status">
                    <?php foreach ($statuses as $st): ?>
                        <?php $isCurrent = ($st['nama'] === ($laporan['status_nama'] ?? '')); ?>
                        <button
                            type="button"
                            class="b <?= esc($st['nama'], 'attr') ?> chip-status<?= $isCurrent ? ' active' : '' ?>"
                            data-status="<?= esc($st['nama'], 'attr') ?>"
                        ><?= esc($st['nama']) ?></button>
                    <?php endforeach; ?>
                </div>

                <div class="g" style="margin-bottom: 16px;">
                    <label for="status-select">Status Baru</label>
                    <select id="status-select" name="status" class="select">
                        <?php foreach ($statuses as $st): ?>
                            <?php $selectedVal = old('status', (string) ($laporan['status_nama'] ?? '')); ?>
                            <option value="<?= esc($st['nama'], 'attr') ?>"<?= $selectedVal === $st['nama'] ? ' selected' : '' ?>>
                                <?= esc($st['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="g" style="margin-bottom: 18px;">
                    <label for="keterangan">Keterangan (Opsional)</label>
                    <textarea
                        id="keterangan"
                        name="keterangan"
                        placeholder="Catatan tindak lanjut teknisi atau keterangan perubahan status..."
                    ><?= esc((string) old('keterangan', '')) ?></textarea>
                </div>

                <button type="submit" class="btn w">Simpan Perubahan Status</button>
            </form>

            <div class="note">
                Setiap perubahan status akan dicatat otomatis pada timeline riwayat laporan beserta nama admin yang memperbarui.
            </div>
        </div>

        <div class="card">
            <h2>Hapus Laporan</h2>
            <p class="lead" style="margin-bottom: 16px;">
                Menghapus laporan ini akan menghapus seluruh riwayat status serta file foto bukti secara permanen.
            </p>
            <?php $deleteUrl = site_url('admin/laporan/' . rawurlencode((string) $laporan['kode'])); ?>
            <button
                type="button"
                class="btn red w"
                onclick="bukaModalHapus(<?= esc(json_encode((string) $laporan['kode']), 'attr') ?>, <?= esc(json_encode((string) $laporan['judul']), 'attr') ?>, <?= esc(json_encode($deleteUrl), 'attr') ?>)"
            >Hapus Laporan</button>
        </div>
    </div>
</div>

<?= $this->include('partials/modal_hapus') ?>
<?= $this->endSection() ?>
