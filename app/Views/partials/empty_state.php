<?php
$heading    = $title ?? ($emptyTitle ?? 'Belum ada laporan');
$message    = $message ?? ($emptyMessage ?? 'Data laporan belum tersedia atau tidak ditemukan sesuai filter yang dipilih.');
$actionUrl  = $actionUrl ?? ($emptyActionUrl ?? null);
$actionText = $actionText ?? ($emptyActionText ?? null);
?>
<div class="empty">
    <div class="ic" aria-hidden="true">
        <div class="ic-doc">
            <div class="ic-line"></div>
            <div class="ic-line"></div>
            <div class="ic-line"></div>
        </div>
    </div>
    <h2><?= esc($heading) ?></h2>
    <p><?= esc($message) ?></p>
    <?php if (! empty($actionUrl) && ! empty($actionText)): ?>
        <a href="<?= esc($actionUrl, 'attr') ?>" class="btn"><?= esc($actionText) ?></a>
    <?php endif; ?>
</div>
