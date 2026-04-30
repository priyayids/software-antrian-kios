<?php
ob_start();
$logoPath = !empty($settings['logo']) && file_exists(__DIR__ . '/../../public/storage/uploads/' . $settings['logo'])
    ? asset('storage/uploads/' . $settings['logo'])
    : asset('storage/uploads/NISCAYA LOGO.png');
?>

<div class="container py-5">
    <div class="alert alert-light mb-4 shadow-sm" role="alert">
        <div class="text-center py-3">
            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo" width="120" class="mb-3">
            <h5 class="fw-bold mb-2"><?= htmlspecialchars($settings['nama_instansi'] ?? 'ANTRIAN NISCAYA') ?></h5>
            <p class="text-muted mb-0">Silahkan pilih halaman yang ingin ditampilkan</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="feature-icon-1 bg-gradient-primary mb-4">
                        <i class="bi-people-fill"></i>
                    </div>
                    <h3 class="h4 fw-bold">Nomor Antrian</h3>
                    <p class="text-muted mb-4">Halaman Nomor Antrian digunakan pengunjung untuk mengambil nomor antrian.</p>
                    <a href="/nomor" class="btn btn-primary rounded-pill px-4 py-2">
                        Tampilkan <i class="bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="feature-icon-1 bg-gradient-success mb-4">
                        <i class="bi-mic-fill"></i>
                    </div>
                    <h3 class="h4 fw-bold">Panggilan Antrian</h3>
                    <p class="text-muted mb-4">Halaman Panggilan Antrian digunakan petugas loket untuk memanggil antrian.</p>
                    <a href="javascript:;" class="btn btn-success rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#panggilAntrian">
                        Tampilkan <i class="bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="feature-icon-1 bg-gradient-info mb-4">
                        <i class="bi-display-fill"></i>
                    </div>
                    <h3 class="h4 fw-bold">Monitor Antrian</h3>
                    <p class="text-muted mb-4">Halaman Monitor Antrian digunakan menampilkan antrian pada monitor.</p>
                    <a href="/monitor" class="btn btn-info text-white rounded-pill px-4 py-2">
                        Tampilkan <i class="bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-4">
                    <div class="feature-icon-1 bg-gradient-warning mb-4">
                        <i class="bi-gear-fill"></i>
                    </div>
                    <h3 class="h4 fw-bold">Setting Antrian</h3>
                    <p class="text-muted mb-4">Halaman Setting Antrian digunakan untuk konfigurasi aplikasi antrian.</p>
                    <a href="/setting" class="btn btn-warning text-white rounded-pill px-4 py-2">
                        Tampilkan <i class="bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="panggilAntrian" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Pilih Loket Antrian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select class="form-select form-select-lg" id="loketAntrian">
                    <option value="" selected>Pilih Loket Antrian</option>
                    <?php
                    $loketList = !empty($settings['list_loket']) ? json_decode($settings['list_loket'], true) : [];
                    foreach ($loketList as $lk):
                    ?>
                        <option value="<?= htmlspecialchars($lk['no_loket']) ?>"><?= htmlspecialchars($lk['nama_loket']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary rounded-pill tampilAntrian">Tampilkan</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$inlineScript = <<<JS
$('.tampilAntrian').click(function() {
    let localLoket = localStorage.getItem("_loket");
    if (localLoket != null) {
        localStorage.removeItem("_loket");
    }
    let loket = $('#loketAntrian').val();
    if (loket != '') {
        localStorage.setItem("_loket", loket);
        window.location.href = "/panggilan";
    } else {
        alert("Silahkan pilih loket terlebih dahulu");
    }
});
JS;
require __DIR__ . '/../layouts/main.php';
?>
