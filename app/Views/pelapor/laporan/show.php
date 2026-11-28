<?= $this->extend('layouts/pelapor') ?>

<?= $this->section('content') ?>
<div class="top">
    <div>
        <a href="<?= site_url('pelapor/laporan') ?>" class="btn gr btn-sm" style="margin-bottom: 12px;">← Kembali</a>
        <h1>Detail Laporan <?= esc($laporan['kode']) ?></h1>
        <p class="lead"><?= esc($laporan['judul']) ?></p>
    </div>
    <div>
        <?= view('partials/status_badge', ['status' => $laporan['status_nama'] ?? 'Menunggu']) ?>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="two">
    <div>
        <section class="card">
            <h2>Informasi Laporan</h2>
            <div style="margin-top: 12px;">
                <div class="row">
                    <span>Kode Laporan</span>
                    <div><strong><?= esc($laporan['kode']) ?></strong></div>
                </div>
                <div class="row">
                    <span>Status</span>
                    <div><?= view('partials/status_badge', ['status' => $laporan['status_nama'] ?? 'Menunggu']) ?></div>
                </div>
                <div class="row">
                    <span>Judul</span>
                    <div><?= esc($laporan['judul']) ?></div>
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
                    <span>Tanggal Lapor</span>
                    <div>
                        <?= ! empty($laporan['created_at']) ? esc(date('d M Y, H:i', strtotime((string) $laporan['created_at']))) : '-' ?>
                    </div>
                </div>
                <div class="row">
                    <span>Deskripsi</span>
                    <div><?= nl2br(esc($laporan['deskripsi'])) ?></div>
                </div>
            </div>
        </section>

        <section class="card">
            <h2 style="margin-bottom: 14px;">Foto Bukti Kerusakan</h2>
            <?php if (! empty($laporan['foto'])): ?>
                <?php
                $fotoRaw = (string) $laporan['foto'];
                $fotoRel = str_starts_with($fotoRaw, 'uploads/') ? $fotoRaw : 'uploads/laporan/' . ltrim($fotoRaw, '/');
                $fotoUrl = base_url($fotoRel);
                $caption = $laporan['kode'] . ' — ' . $laporan['judul'];
                $lightboxCall = 'bukaLightbox(' . json_encode($fotoUrl) . ', ' . json_encode($caption) . ')';
                ?>
                <div
                    class="detail-photo-wrapper"
                    onclick="<?= htmlspecialchars($lightboxCall, ENT_QUOTES, 'UTF-8') ?>"
                    role="button"
                    tabindex="0"
                    onkeydown="<?= htmlspecialchars('if (event.key === "Enter" || event.key === " ") { event.preventDefault(); ' . $lightboxCall . '; }', ENT_QUOTES, 'UTF-8') ?>"
                >
                    <img
                        src="<?= esc($fotoUrl, 'attr') ?>"
                        alt="Foto bukti <?= esc($laporan['kode']) ?>"
                        style="width: 100%; max-height: 420px; object-fit: cover; display: block;"
                    >
                    <div class="detail-photo-overlay">
                        <span>Klik untuk memperbesar foto</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="ph" style="width: 100%;">Tidak ada foto bukti terlampir</div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="card">
        <h2>Riwayat Perkembangan</h2>

        <?php if (empty($riwayat)): ?>
            <p class="lead" style="margin-top: 12px;">Belum ada riwayat perkembangan status.</p>
        <?php else: ?>
            <div class="tl">
                <?php $totalRiwayat = count($riwayat); ?>
                <?php foreach ($riwayat as $idx => $log): ?>
                    <div class="tl-item<?= $idx === $totalRiwayat - 1 ? ' on' : '' ?>">
                        <b><?= esc($log['status_nama'] ?? 'Menunggu') ?></b>
                        <?php if (! empty($log['keterangan'])): ?>
                            <div style="margin-top: 4px; font-size: 13px; color: var(--tx);">
                                <?= esc($log['keterangan']) ?>
                            </div>
                        <?php endif; ?>
                        <small>
                            <?= ! empty($log['created_at']) ? esc(date('d M Y, H:i', strtotime((string) $log['created_at']))) : '-' ?>
                            <?php if (! empty($log['pengubah_nama'])): ?>
                                &bull; <?= esc($log['pengubah_nama']) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="note">
            Status laporan saat ini: <strong><?= esc($laporan['status_nama'] ?? 'Menunggu') ?></strong>.
            Setiap perubahan status oleh admin akan tercatat otomatis pada timeline di atas.
        </div>
    </aside>
</div>
<?= $this->endSection() ?>
