<?php
$rawStatus = $status ?? ($laporan['status_nama'] ?? 'Menunggu');
if (is_array($rawStatus)) {
    $rawStatus = $rawStatus['nama'] ?? ($rawStatus['status_nama'] ?? 'Menunggu');
}
$label      = trim((string) $rawStatus);
$allowed    = ['Menunggu', 'Diproses', 'Selesai'];
$badgeClass = in_array($label, $allowed, true) ? $label : 'Menunggu';
$classAttr  = 'b ' . $badgeClass . (! empty($extraClass) ? ' ' . $extraClass : '');
?>
<span class="<?= esc($classAttr, 'attr') ?>"><?= esc($label) ?></span>
