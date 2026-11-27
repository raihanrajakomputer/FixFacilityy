<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'FixFacility - Portal Pelapor') ?></title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body>
<?php
if (! function_exists('uri_string')) {
    helper('url');
}
$uriPath   = trim(uri_string(), '/');
$activeNav = $activeMenu ?? '';

$isDashboard = $activeNav === 'dashboard'
    || ($activeNav === '' && ($uriPath === 'pelapor' || str_starts_with($uriPath, 'pelapor/dashboard')));
$isBuat = $activeNav === 'buat'
    || ($activeNav === '' && (
        str_starts_with($uriPath, 'pelapor/laporan/buat')
        || str_starts_with($uriPath, 'pelapor/laporan/create')
        || str_starts_with($uriPath, 'pelapor/buat')
    ));
$isLaporan = $activeNav === 'laporan'
    || ($activeNav === '' && ! $isBuat && str_starts_with($uriPath, 'pelapor/laporan'));
?>
<div class="app">
    <aside class="side">
        <div class="logo">FixFacility</div>
        <div class="sub">Portal Pelapor</div>

        <nav class="side-nav" aria-label="Navigasi Pelapor">
            <a href="<?= site_url('pelapor/dashboard') ?>" class="nav<?= $isDashboard ? ' on' : '' ?>">Dashboard</a>
            <a href="<?= site_url('pelapor/laporan') ?>" class="nav<?= $isLaporan ? ' on' : '' ?>">Laporan Saya</a>
            <a href="<?= site_url('pelapor/laporan/buat') ?>" class="nav<?= $isBuat ? ' on' : '' ?>">Buat Laporan</a>
        </nav>

        <button type="button" class="nav out" onclick="bukaModalLogout()">Keluar</button>
    </aside>

    <main class="main">
        <?= $this->renderSection('content') ?>
    </main>
</div>

<div id="modal-konfirmasi-logout" class="mask" role="dialog" aria-modal="true" aria-labelledby="modal-logout-title">
    <div class="mod">
        <div class="x" aria-hidden="true">!</div>
        <h2 id="modal-logout-title">Anda akan keluar, yakin?</h2>
        <p>Sesi Anda akan diakhiri dan Anda perlu login kembali untuk mengakses FixFacility.</p>
        <div class="acts">
            <button type="button" class="btn gr" onclick="tutupModalLogout()">Batal</button>
            <form action="<?= site_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn red w">Yakin</button>
            </form>
        </div>
    </div>
</div>

<div id="modal-lightbox" class="lightbox-modal" onclick="tutupLightbox()" role="dialog" aria-modal="true" aria-label="Pratinjau foto laporan">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button type="button" class="lightbox-close" onclick="tutupLightbox()" aria-label="Tutup">&times;</button>
        <img id="lightbox-img" src="" alt="Pratinjau Foto Penuh">
        <div id="lightbox-caption" class="lightbox-caption"></div>
    </div>
</div>

<script src="<?= base_url('js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
