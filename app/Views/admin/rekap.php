<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="top">
    <div>
        <h1>Rekap Laporan</h1>
        <p class="lead">Ringkasan statistik laporan kerusakan fasilitas periode <strong><?= esc($selectedLabel) ?></strong></p>
    </div>

    <div style="width: 240px">
        <label for="rekap-periode" style="margin-bottom: 6px;">Periode Laporan</label>
        <select id="rekap-periode" name="periode" class="select" aria-label="Pilih periode rekap laporan">
            <?php foreach ($periodeOptions as $opt): ?>
                <option value="<?= esc($opt['value'], 'attr') ?>"<?= $opt['value'] === $selectedPeriode ? ' selected' : '' ?>>
                    <?= esc($opt['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="grid4">
    <div class="stat">
        <span class="stat-label">Total Laporan</span>
        <span class="stat-val"><?= esc((string) $totalLaporan) ?></span>
        <span class="stat-sub">Periode <?= esc($selectedLabel) ?></span>
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
    <h2 style="margin-bottom: 14px;">Komposisi Status Laporan</h2>
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
    <h2 style="margin-bottom: 18px;">Rincian Laporan Per Kategori</h2>

    <?php if ($totalLaporan === 0): ?>
        <?= view('partials/empty_state', [
            'title'   => 'Belum ada laporan pada periode ini',
            'message' => 'Tidak ditemukan laporan kerusakan fasilitas untuk periode ' . $selectedLabel . '.',
        ]) ?>
    <?php else: ?>
        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Jumlah Laporan</th>
                        <th>Persentase</th>
                        <th style="min-width: 220px;">Proporsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategoriBreakdown as $kat): ?>
                        <tr>
                            <td><strong><?= esc($kat['nama']) ?></strong></td>
                            <td><?= esc((string) $kat['count']) ?> laporan</td>
                            <td class="muted"><?= esc((string) $kat['persentase']) ?>%</td>
                            <td>
                                <div class="bar" style="margin: 0;">
                                    <div class="bar-track">
                                        <u style="width: <?= esc((string) $kat['persentase'], 'attr') ?>%; background: var(--pr);"></u>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const periodeSelect = document.getElementById('rekap-periode');
    if (periodeSelect) {
        periodeSelect.addEventListener('change', () => {
            const baseUrl = <?= json_encode(site_url('admin/rekap')) ?>;
            const periode = encodeURIComponent(periodeSelect.value);
            window.location.href = `${baseUrl}?periode=${periode}`;
        });
    }
});
</script>
<?= $this->endSection() ?>
