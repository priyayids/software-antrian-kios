<?php
ob_start();
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row px-4 py-3 mb-4 bg-white rounded-2 shadow-sm">
        <div class="d-flex align-items-center me-md-auto">
            <i class="bi-mic-fill text-primary me-3 fs-3"></i>
            <h1 class="h5 fw-bold mb-0">Panggilan Antrian <span class="namaLoket"></span></h1>
        </div>
        <div class="ms-5 ms-md-0 pt-md-2">
            <nav style="--bs-breadcrumb-divider: '>';">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/" class="text-decoration-none"><i class="bi-house-fill text-primary"></i></a></li>
                    <li class="breadcrumb-item active">Panggilan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi-people-fill"></i>
                        </div>
                        <div>
                            <p id="jumlah-antrian" class="fs-3 fw-bold text-warning mb-0">-</p>
                            <p class="text-muted mb-0 small">Jumlah Antrian</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi-person-check-fill"></i>
                        </div>
                        <div>
                            <p id="antrian-sekarang" class="fs-3 fw-bold text-success mb-0">-</p>
                            <p class="text-muted mb-0 small">Antrian Sekarang</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi-person-plus-fill"></i>
                        </div>
                        <div>
                            <p id="antrian-selanjutnya" class="fs-3 fw-bold text-info mb-0">-</p>
                            <p class="text-muted mb-0 small">Antrian Selanjutnya</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                            <i class="bi-person-x-fill"></i>
                        </div>
                        <div>
                            <p id="sisa-antrian" class="fs-3 fw-bold text-danger mb-0">-</p>
                            <p class="text-muted mb-0 small">Sisa Antrian</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="tabel-antrian" class="table table-hover" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center">Nomor Antrian</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Panggil</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCss = ['assets/vendor/css/datatables.min.css'];
$extraJs = ['assets/vendor/js/datatables.min.js'];
$inlineScript = <<<JS
$(document).ready(function() {
    var loket = localStorage.getItem('_loket') || '-';
    $(".namaLoket").html(' Loket ' + loket);

    function loadStats() {
        $('#jumlah-antrian').load('/api/panggilan/jumlah');
        $('#antrian-sekarang').load('/api/panggilan/sekarang');
        $('#antrian-selanjutnya').load('/api/panggilan/selanjutnya');
        $('#sisa-antrian').load('/api/panggilan/sisa');
    }

    loadStats();

    var table = $('#tabel-antrian').DataTable({
        "lengthChange": false,
        "searching": false,
        "ajax": {
            "url": "/api/panggilan/antrian",
            "dataSrc": "data"
        },
        "columns": [
            {
                "data": "no_antrian",
                "width": '200px',
                "orderable": false,
                "searchable": false,
                "className": 'text-center',
                "render": function(data) {
                    return '<span class="badge bg-light text-dark fs-6 px-3 py-2">' + data + '</span>';
                }
            },
            {
                "data": "status",
                "visible": false
            },
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                "width": '100px',
                "className": 'text-center',
                "render": function(data) {
                    if (data["status"] === "0") {
                        return '<button class="btn btn-success btn-sm rounded-circle btn-panggil" data-id="' + data["id"] + '" data-antrian="' + data["no_antrian"] + '"><i class="bi-mic-fill"></i></button>';
                    } else if (data["status"] === "1") {
                        return '<button class="btn btn-secondary btn-sm rounded-circle btn-panggil" data-id="' + data["id"] + '" data-antrian="' + data["no_antrian"] + '"><i class="bi-mic-fill"></i></button>';
                    }
                    return '-';
                }
            }
        ],
        "order": [[0, "desc"]],
        "iDisplayLength": 10,
    });

    $('#tabel-antrian tbody').on('click', '.btn-panggil', function() {
        var antrian = $(this).data('antrian');
        var id = $(this).data('id');

        $.ajax({
            url: "/api/panggilan/create",
            type: "POST",
            dataType: 'json',
            data: {
                antrian: antrian,
                loket: loket
            },
            success: function() {
                $.ajax({
                    type: "POST",
                    url: "/api/panggilan/update",
                    data: { id: id }
                });
                table.ajax.reload(null, false);
                loadStats();
            }
        });
    });

    setInterval(function() {
        table.ajax.reload(null, false);
        loadStats();
    }, 1000);
});
JS;
require __DIR__ . '/../../layouts/main.php';
?>
