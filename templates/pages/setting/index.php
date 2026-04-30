<?php
ob_start();
$settingsData = $settings ?? [];
$listLoket = !empty($settingsData['list_loket']) ? json_decode($settingsData['list_loket'], true) : [];
$logoPath = !empty($settingsData['logo']) && file_exists(__DIR__ . '/../../public/storage/uploads/' . $settingsData['logo'])
    ? asset('storage/uploads/' . $settingsData['logo'])
    : asset('storage/uploads/NISCAYA LOGO.png');

if (isset($_SESSION['login_error'])) {
    $loginError = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
} else {
    $loginError = null;
}
?>

<div class="container py-4">
    <?php if (!$isLoggedIn): ?>
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <i class="bi-lock-fill text-primary fs-2 mb-3"></i>
                        <h4 class="fw-bold">Login Admin</h4>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <?php if ($loginError): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
                        <?php endif; ?>
                        <form action="/setting/login" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-medium">Username</label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-medium">Password</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill">
                                <i class="bi-unlock-fill me-2"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column flex-md-row px-4 py-3 mb-4 bg-white rounded-2 shadow-sm">
            <div class="d-flex align-items-center me-md-auto">
                <i class="bi-gear-fill text-primary me-3 fs-3"></i>
                <h1 class="h5 fw-bold mb-0">Setting Aplikasi Antrian</h1>
            </div>
            <div class="ms-5 ms-md-0 pt-md-2">
                <nav style="--bs-breadcrumb-divider: '>';">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/" class="text-decoration-none"><i class="bi-house-fill text-primary"></i></a></li>
                        <li class="breadcrumb-item active">Setting</li>
                    </ol>
                </nav>
            </div>
        </div>

        <form id="saveSetting" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($settingsData['id'] ?? '') ?>">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold">Informasi Instansi</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="nama_instansi" class="form-label fw-medium">Nama Instansi</label>
                                <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" value="<?= htmlspecialchars($settingsData['nama_instansi'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label fw-medium">Alamat Lengkap</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($settingsData['alamat'] ?? '') ?></textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="telpon" class="form-label fw-medium">Telepon</label>
                                    <input type="text" class="form-control" id="telpon" name="telpon" value="<?= htmlspecialchars($settingsData['telpon'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-medium">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($settingsData['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="running_text" class="form-label fw-medium">Running Text</label>
                                <textarea class="form-control" id="running_text" name="running_text" rows="3" required><?= htmlspecialchars($settingsData['running_text'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="youtube_id" class="form-label fw-medium">YouTube ID</label>
                                <input type="text" class="form-control" id="youtube_id" name="youtube_id" value="<?= htmlspecialchars($settingsData['youtube_id'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-bold">Daftar Loket</div>
                        <div class="card-body">
                            <div id="loketContainer">
                                <?php if (count($listLoket) > 0): ?>
                                    <?php foreach ($listLoket as $keyLk => $valLk): ?>
                                        <div class="row block_row g-2 mb-2 align-items-end">
                                            <div class="col-11">
                                                <div class="row g-2">
                                                    <div class="col-3">
                                                        <label class="form-label small <?= $keyLk !== 0 ? 'd-none' : '' ?>">Nomor Loket</label>
                                                        <input type="text" class="form-control" name="no_loket[]" value="<?= htmlspecialchars($valLk['no_loket'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="col-9">
                                                        <label class="form-label small <?= $keyLk !== 0 ? 'd-none' : '' ?>">Nama Loket</label>
                                                        <input type="text" class="form-control" name="nama_loket[]" value="<?= htmlspecialchars($valLk['nama_loket'] ?? '') ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <?php if ($keyLk === 0): ?>
                                                    <button type="button" class="btn btn-success btn-sm addLoket w-100"><i class="bi-plus-lg"></i></button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-danger btn-sm deleteLoket w-100"><i class="bi-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="row block_row g-2 mb-2 align-items-end">
                                        <div class="col-11">
                                            <div class="row g-2">
                                                <div class="col-3">
                                                    <label class="form-label small">Nomor Loket</label>
                                                    <input type="text" class="form-control" name="no_loket[]" required>
                                                </div>
                                                <div class="col-9">
                                                    <label class="form-label small">Nama Loket</label>
                                                    <input type="text" class="form-control" name="nama_loket[]" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-success btn-sm addLoket w-100"><i class="bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold">Styling Monitor</div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <img id="logoPreview" src="<?= htmlspecialchars($logoPath) ?>" class="rounded shadow-sm" alt="Logo" style="max-width: 150px; max-height: 150px;">
                            </div>

                            <div class="mb-3">
                                <label for="logo" class="form-label fw-medium">Pilih Logo</label>
                                <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                                <input type="hidden" name="nama_logo" value="<?= htmlspecialchars($settingsData['logo'] ?? '') ?>">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="warna_primary" class="form-label small">Warna Primary</label>
                                    <input type="color" class="form-control form-control-color w-100" id="warna_primary" name="warna_primary" value="<?= htmlspecialchars($settingsData['warna_primary'] ?? '#00923f') ?>" required>
                                </div>
                                <div class="col-6">
                                    <label for="warna_secondary" class="form-label small">Warna Secondary</label>
                                    <input type="color" class="form-control form-control-color w-100" id="warna_secondary" name="warna_secondary" value="<?= htmlspecialchars($settingsData['warna_secondary'] ?? '#1dedae') ?>" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="warna_accent" class="form-label small">Warna Accent</label>
                                    <input type="color" class="form-control form-control-color w-100" id="warna_accent" name="warna_accent" value="<?= htmlspecialchars($settingsData['warna_accent'] ?? '#6083a9') ?>" required>
                                </div>
                                <div class="col-6">
                                    <label for="warna_background" class="form-label small">Warna Background</label>
                                    <input type="color" class="form-control form-control-color w-100" id="warna_background" name="warna_background" value="<?= htmlspecialchars($settingsData['warna_background'] ?? '#5dee9c') ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="warna_text" class="form-label small">Warna Text</label>
                                <input type="color" class="form-control form-control-color w-100" id="warna_text" name="warna_text" value="<?= htmlspecialchars($settingsData['warna_text'] ?? '#ffffff') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1 rounded-pill">
                            <i class="bi-save-fill me-2"></i> Simpan
                        </button>
                        <button type="button" id="logout" class="btn btn-danger btn-lg rounded-pill">
                            <i class="bi-box-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
$loketTemplate = '<div class="row block_row g-2 mb-2 align-items-end">
    <div class="col-11">
        <div class="row g-2">
            <div class="col-3">
                <input type="text" class="form-control" name="no_loket[]" placeholder="Nomor" required>
            </div>
            <div class="col-9">
                <input type="text" class="form-control" name="nama_loket[]" placeholder="Nama Loket" required>
            </div>
        </div>
    </div>
    <div class="col-1">
        <button type="button" class="btn btn-danger btn-sm deleteLoket w-100"><i class="bi-trash"></i></button>
    </div>
</div>';
?>

<?php
$content = ob_get_clean();
$inlineScript = <<<JS
const loketTemplate = `{$loketTemplate}`;

$(document).on("click", ".addLoket", function(e) {
    $("#loketContainer").append(loketTemplate);
});

$(document).on("click", ".deleteLoket", function(e) {
    $(this).closest(".block_row").remove();
});

$("#logo").change(function() {
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $("#logoPreview").attr("src", e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    }
});

$(document).on("submit", "#saveSetting", function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        type: 'POST',
        url: '/api/setting/save',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var logoFile = $("#logo")[0].files[0];
                if (logoFile) {
                    $("#logoPreview").attr("src", "/storage/uploads/" + logoFile.name);
                    $("input[name='nama_logo']").val(logoFile.name);
                    $("#logo").val("");
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message || 'Pengaturan berhasil disimpan.',
                    confirmButtonColor: '#667eea',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal Menyimpan',
                    text: response.message || 'Terjadi kesalahan saat menyimpan pengaturan.',
                    confirmButtonColor: '#667eea',
                    confirmButtonText: 'Coba Lagi'
                });
            }
        },
        error: function(xhr) {
            var message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.status === 401) {
                message = 'Sesi Anda telah berakhir. Silakan login kembali.';
            }
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: message,
                confirmButtonColor: '#667eea',
                confirmButtonText: 'Mengerti'
            });
        }
    });
});

$(document).on("click", "#logout", function(e) {
    $.ajax({
        type: 'POST',
        url: '/setting/logout',
        success: function(result) {
            if (result === 'Success') {
                window.location.reload();
            }
        }
    });
});
JS;
require __DIR__ . '/../../layouts/main.php';
?>
