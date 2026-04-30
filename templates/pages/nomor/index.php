<?php
ob_start();
$logoPath = !empty($settings['logo']) && file_exists(__DIR__ . '/../../public/storage/uploads/' . $settings['logo'])
    ? asset('storage/uploads/' . $settings['logo'])
    : asset('assets/img/default.png');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi-people-fill text-primary me-3 fs-2"></i>
                            <h1 class="h4 fw-bold mb-0">Nomor Antrian</h1>
                        </div>
                        <a href="/" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Kembali ke Beranda">
                            <i class="bi-house-fill text-primary"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="queue-display mb-5">
                        <h3 class="text-muted fw-light mb-3">ANTRIAN</h3>
                        <h1 id="antrian" class="display-1 fw-bolder text-primary queue-number"></h1>
                    </div>

                    <a id="insert" href="javascript:void(0)" class="btn btn-primary btn-lg rounded-pill px-5 py-3 take-number-btn">
                        <i class="bi-person-plus me-2"></i> Ambil Nomor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$inlineScript = <<<JS
$(document).ready(function() {
    function loadAntrian() {
        $.get('/api/nomor/antrian', function(data) {
            $('#antrian').html(data).fadeIn('slow');
        });
    }

    loadAntrian();

    let isProcessing = false;

    $('#insert').on('click', function() {
        if (isProcessing) return;
        isProcessing = true;

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Memproses...');

        $.ajax({
            type: 'POST',
            url: '/api/nomor/insert',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#antrian').html(response.no_antrian).fadeIn('slow');

                    if (response.print_status === 'printer_error') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Antrian Berhasil Diambil',
                            html: 'Nomor antrian Anda: <strong>' + response.no_antrian + '</strong><br><br>Pengambilan nomor berhasil, namun printer tidak merespons. Harap hubungi petugas untuk mencetak tiket Anda.',
                            confirmButtonColor: '#667eea',
                            confirmButtonText: 'Mengerti'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: 'Nomor antrian Anda: <strong>' + response.no_antrian + '</strong><br><br>Silakan menunggu hingga nomor antrian Anda dipanggil.',
                            confirmButtonColor: '#667eea',
                            confirmButtonText: 'Mengerti'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan saat mengambil nomor antrian.',
                        confirmButtonColor: '#667eea'
                    });
                }

                isProcessing = false;
                btn.prop('disabled', false).html('<i class="bi-person-plus me-2"></i> Ambil Nomor');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Terjadi kesalahan pada sistem. Silakan coba lagi atau hubungi IT Support.',
                    confirmButtonColor: '#667eea'
                });

                isProcessing = false;
                btn.prop('disabled', false).html('<i class="bi-person-plus me-2"></i> Ambil Nomor');
            }
        });
    });
});
JS;
require __DIR__ . '/../../layouts/main.php';
?>
