<?= $this->extend('layouts/pelapor') ?>

<?= $this->section('content') ?>
<?php
$errors = session()->getFlashdata('errors') ?? [];
if (! is_array($errors)) {
    $errors = [];
}
$hasValidationError = ! empty($errors);
?>
<div class="top">
    <div>
        <a href="<?= site_url('pelapor/laporan') ?>" class="btn gr btn-sm" style="margin-bottom: 12px;">← Kembali</a>
        <h1>Buat Laporan Kerusakan</h1>
        <p class="lead">Lengkapi rincian fasilitas yang rusak beserta foto bukti agar segera ditindaklanjuti.</p>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div id="alert-error-form" class="alert<?= $hasValidationError ? ' alert-error show' : '' ?>">
    <b>Periksa kembali data laporan Anda.</b>
    <?php if ($hasValidationError): ?>
        <ul style="margin: 6px 0 0 18px; padding: 0;">
            <?php foreach ($errors as $msg): ?>
                <li><?= esc($msg) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <span>Pastikan seluruh kolom wajib telah diisi dan foto bukti telah diunggah.</span>
    <?php endif; ?>
</div>

<section class="card">
    <form id="form-buat-laporan" action="<?= site_url('pelapor/laporan') ?>" method="post" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <div class="fl">
            <div class="g full<?= isset($errors['judul']) ? ' err' : '' ?>">
                <label for="judul">Judul Laporan</label>
                <input
                    type="text"
                    id="judul"
                    name="judul"
                    value="<?= esc(old('judul')) ?>"
                    placeholder="Contoh: Lampu ruang kelas berkedip dan mati total"
                >
                <div class="em"><?= esc($errors['judul'] ?? 'Judul laporan wajib diisi.') ?></div>
            </div>

            <div class="g<?= isset($errors['fasilitas_id']) ? ' err' : '' ?>">
                <label for="fasilitas_id">Fasilitas</label>
                <select id="fasilitas_id" name="fasilitas_id" class="select">
                    <option value="">-- Pilih Fasilitas --</option>
                    <?php foreach ($fasilitasList ?? [] as $fasilitas): ?>
                        <option value="<?= esc((string) $fasilitas['id']) ?>" <?= (string) old('fasilitas_id') === (string) $fasilitas['id'] ? 'selected' : '' ?>>
                            <?= esc($fasilitas['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="em"><?= esc($errors['fasilitas_id'] ?? 'Fasilitas wajib dipilih.') ?></div>
            </div>

            <div class="g<?= isset($errors['kategori_id']) ? ' err' : '' ?>">
                <label for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="select">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategoriList ?? [] as $kategori): ?>
                        <option value="<?= esc((string) $kategori['id']) ?>" <?= (string) old('kategori_id') === (string) $kategori['id'] ? 'selected' : '' ?>>
                            <?= esc($kategori['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="em"><?= esc($errors['kategori_id'] ?? 'Kategori wajib dipilih.') ?></div>
            </div>

            <div class="g full<?= isset($errors['lokasi']) ? ' err' : '' ?>">
                <label for="lokasi">Lokasi</label>
                <input
                    type="text"
                    id="lokasi"
                    name="lokasi"
                    value="<?= esc(old('lokasi')) ?>"
                    placeholder="Contoh: Gedung A, Lantai 2, Ruang A203"
                >
                <div class="em"><?= esc($errors['lokasi'] ?? 'Lokasi kerusakan wajib diisi.') ?></div>
            </div>

            <div class="g full<?= isset($errors['deskripsi']) ? ' err' : '' ?>">
                <label for="deskripsi">Deskripsi Kerusakan</label>
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    placeholder="Jelaskan kondisi kerusakan secara rinci..."
                ><?= esc(old('deskripsi')) ?></textarea>
                <div class="em"><?= esc($errors['deskripsi'] ?? 'Deskripsi kerusakan wajib diisi.') ?></div>
            </div>

            <div id="group-foto" class="g full<?= isset($errors['foto']) ? ' err' : '' ?>">
                <label for="input-foto">Foto Bukti Kerusakan</label>
                <input
                    type="file"
                    id="input-foto"
                    name="foto"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    style="display: none;"
                >

                <div id="drop-foto" class="drop<?= isset($errors['foto']) ? ' is-invalid' : '' ?>">
                    <b id="drop-title">Klik atau seret foto ke sini untuk mengunggah</b>
                    <small id="drop-subtitle">Format JPG, JPEG, PNG, atau WEBP (Maksimal 5 MB)</small>
                </div>

                <div id="foto-preview-wrapper" class="upload-preview-wrapper" style="display: none;">
                    <div class="upload-preview-card">
                        <img id="foto-preview-img" class="upload-preview-thumb" src="" alt="Pratinjau foto laporan">
                        <div class="upload-preview-meta">
                            <div class="upload-preview-info">
                                <span id="foto-preview-name" class="upload-preview-filename"></span>
                                <span id="foto-preview-size" class="upload-preview-filesize"></span>
                            </div>
                            <div class="upload-preview-actions">
                                <button type="button" id="btn-ganti-foto" class="btn btn-sm btn-outline">Ganti Foto</button>
                                <button type="button" id="btn-hapus-foto" class="btn btn-sm btn-danger-outline">Hapus Foto</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="foto-error-msg" class="em"<?= isset($errors['foto']) ? ' style="display: block;"' : '' ?>>
                    <?= esc($errors['foto'] ?? 'Foto bukti kerusakan wajib diunggah.') ?>
                </div>

                <?php if ($hasValidationError): ?>
                    <small class="muted" style="display: block; margin-top: 6px;">Pilih ulang foto Anda jika sebelumnya sudah dipilih.</small>
                <?php endif; ?>
            </div>

            <div class="full" style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 6px;">
                <button type="submit" class="btn">Kirim Laporan</button>
                <a href="<?= site_url('pelapor/laporan') ?>" class="btn gr">Batal</a>
            </div>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
