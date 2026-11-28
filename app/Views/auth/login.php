<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - FixFacility</title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body>
<?php
helper(['form', 'url']);
$errors     = validation_errors();
$nimError   = $errors['nim'] ?? '';
$passError  = $errors['password'] ?? '';
$loginError = session()->getFlashdata('error');
?>
<div class="login">
    <section class="lf">
        <div>
            <h1>FixFacility</h1>
            <p>Sistem Pelaporan Kerusakan Fasilitas Kampus</p>
        </div>

        <div class="hero">
            <h3>LAPORKAN KERUSAKAN FASILITAS DENGAN CEPAT</h3>
            <p>Sampaikan kondisi fasilitas kampus yang bermasalah dan pantau proses penanganannya secara transparan hingga selesai.</p>
        </div>
    </section>

    <section class="lr">
        <form id="form-login" action="<?= site_url('login') ?>" method="post" novalidate>
            <?= csrf_field() ?>

            <div style="margin-bottom: 24px;">
                <h1>Masuk ke Akun</h1>
                <p class="lead">Gunakan NIM atau NIP Anda untuk melanjutkan ke FixFacility.</p>
            </div>

            <?php if (! empty($loginError)) : ?>
                <div class="alert alert-error" role="alert">
                    <b>Gagal Masuk</b>
                    <?= esc($loginError) ?>
                </div>
            <?php endif; ?>

            <div id="g-nim" class="g<?= $nimError !== '' ? ' err' : '' ?>" style="margin-bottom: 18px;">
                <label for="nim">NIM / NIP</label>
                <input
                    type="text"
                    id="nim"
                    name="nim"
                    value="<?= esc(old('nim') ?? '') ?>"
                    placeholder="Masukkan NIM atau NIP"
                    autocomplete="username"
                    autofocus
                >
                <div id="e1" class="em"><?= esc($nimError) ?></div>
            </div>

            <div id="g-password" class="g<?= $passError !== '' ? ' err' : '' ?>" style="margin-bottom: 24px;">
                <label for="password">Kata Sandi</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    autocomplete="current-password"
                >
                <div id="e2" class="em"><?= esc($passError) ?></div>
            </div>

            <button type="submit" class="btn w">Masuk</button>
        </form>
    </section>
</div>

<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
