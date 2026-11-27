<div id="modal-konfirmasi-hapus" class="mask" role="dialog" aria-modal="true" aria-labelledby="modal-hapus-title">
    <div class="mod">
        <div class="x" aria-hidden="true">!</div>
        <h2 id="modal-hapus-title">Hapus laporan ini?</h2>
        <p id="modal-hapus-desc">Laporan yang dihapus tidak dapat dikembalikan lagi.</p>
        <div class="acts">
            <button type="button" class="btn gr" onclick="tutupModalHapus()">Batal</button>
            <form id="modal-hapus-form" action="" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn red w">Hapus</button>
            </form>
        </div>
    </div>
</div>
