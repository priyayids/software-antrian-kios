<?php
ob_start();
$logoPath = !empty($settings['logo']) && file_exists(BASE_PATH . '/public/storage/uploads/' . $settings['logo'])
    ? asset('storage/uploads/' . $settings['logo']) . '?v=' . filemtime(BASE_PATH . '/public/storage/uploads/' . $settings['logo'])
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
$inlineScript = <<<'JS'
$(document).ready(function() {
    function loadAntrian() {
        $.get('/api/nomor/antrian', function(data) {
            $('#antrian').html(data).fadeIn('slow');
        });
    }

    loadAntrian();

    let isProcessing = false;

    const isAndroid = /android/i.test(navigator.userAgent);
    console.log('[ANTRIAN] Android detected:', isAndroid);

    // ---- ESC/POS helpers for client-side printing ----
    function generateReceipt(noAntrian) {
        const ESC = 0x1B, GS = 0x1D, LF = 0x0A;
        const buf = [];
        function push() { buf.push.apply(buf, arguments); }
        function text(str) {
            for (var i = 0; i < str.length; i++) buf.push(str.charCodeAt(i));
        }
        function dateIndo() {
            var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            var d = new Date();
            return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        }

        // Initialize printer
        push(ESC, 0x40);
        // Center
        push(ESC, 0x61, 0x01);
        // Emphasis on, Font A, double width
        push(ESC, 0x45, 0x01);
        push(ESC, 0x4D, 0x00);
        push(GS, 0x21, 0x01);
        text("PT NISCAYA UNGGUL NUSANTARA\n");
        push(ESC, 0x21, 0x00);
        // Font B, normal size
        push(ESC, 0x4D, 0x01);
        push(GS, 0x21, 0x00);
        text("Rukan graha mas Jl. Pejuangan No.C 11,\n");
        text("RT.1/RW.7, Kebon Jeruk, Kebonjeruk,\n");
        text("West Jakarta City, Jakarta 11520\n");
        push(LF);
        // Nomor antrian header — emphasis, Font B, double width
        push(ESC, 0x45, 0x01);
        push(ESC, 0x4D, 0x01);
        push(GS, 0x21, 0x01);
        text("NOMOR ANTRIAN ANDA\n");
        push(ESC, 0x21, 0x00);
        // Number — emphasis, Font A, 6x6
        push(ESC, 0x45, 0x01);
        push(ESC, 0x4D, 0x00);
        push(GS, 0x21, 0x33);
        text(noAntrian + "\n\n");
        push(ESC, 0x21, 0x00);
        // Body — Font B, normal size
        push(ESC, 0x4D, 0x01);
        push(GS, 0x21, 0x00);
        text("Silakan menunggu hingga nomor antrian\n");
        text("Anda dipanggil.\n");
        text("Nomor ini hanya berlaku pada hari ini.\n");
        push(LF);
        // Date
        push(ESC, 0x4D, 0x01);
        push(GS, 0x21, 0x00);
        text(dateIndo() + "\n");
        push(LF);
        // Thanks — emphasis, Font B
        push(ESC, 0x45, 0x01);
        push(ESC, 0x4D, 0x01);
        push(GS, 0x21, 0x00);
        text("TERIMA KASIH\n");
        push(ESC, 0x21, 0x00);
        // Feed 3 + cut
        push(ESC, 0x64, 0x03);
        push(ESC, 0x6D);

        return new Uint8Array(buf);
    }

    // ---- WebUSB client-side printing (requires HTTPS) ----
    async function tryWebUsb(number) {
        if (!navigator.usb) return false;
        var device = null;
        try {
            // Try already-permitted devices first
            var devices = await navigator.usb.getDevices();
            for (var i = 0; i < devices.length; i++) {
                var d = devices[i];
                console.log('[ANTRIAN] Found permitted USB device:', d.productName, d.manufacturerName);
                try {
                    await d.open();
                    await d.selectConfiguration(1);
                    await d.claimInterface(0);
                    var ep = d.configuration.interfaces[0].alternate.endpoints.find(function(e) {
                        return e.direction === 'out' && e.type === 'bulk';
                    });
                    if (!ep) { await d.close(); continue; }
                    device = d;
                    console.log('[ANTRIAN] Using permitted device:', device.productName);
                    break;
                } catch (e) {
                    console.log('[ANTRIAN] Skipping device:', d.productName, e.message);
                    try { await d.close(); } catch (_) {}
                }
            }
            // No permitted device found, ask user to pick one
            if (!device) {
                device = await navigator.usb.requestDevice({ filters: [
                    { classCode: 7 },   // Printer class
                    { classCode: 255 }, // Vendor-specific
                    { classCode: 2 },   // CDC Communications
                ] });
                console.log('[ANTRIAN] User selected device:', device.productName);
                await device.open();
                await device.selectConfiguration(1);
                await device.claimInterface(0);
            }
            var endpoint = device.configuration.interfaces[0].alternate.endpoints.find(function(e) {
                return e.direction === 'out' && e.type === 'bulk';
            });
            if (!endpoint) throw new Error('No bulk OUT endpoint on ' + device.productName);
            var data = generateReceipt(number);
            await device.transferOut(endpoint.endpointNumber, data);
            await device.close();
            console.log('[ANTRIAN] WebUSB print success');
            return true;
        } catch (e) {
            console.log('[ANTRIAN] WebUSB failed:', e.message);
            if (device) { try { await device.close(); } catch (_) {} }
            return false;
        }
    }

    // ---- RawBT localhost HTTP API fallback ----
    async function tryRawBtLocalhost(number) {
        var data = generateReceipt(number);
        try {
            var res = await fetch('http://127.0.0.1:9100/', {
                method: 'POST',
                headers: { 'Content-Type': 'application/octet-stream' },
                body: data
            });
            return res.ok;
        } catch (e) {
            console.log('[ANTRIAN] RawBT localhost failed:', e);
            return false;
        }
    }

    // ---- Client-side print chain ----
    async function tryClientPrint(number) {
        if (navigator.usb) {
            if (await tryWebUsb(number)) return true;
        }
        return await tryRawBtLocalhost(number);
    }

    function safeSwal(opts) {
        if (typeof Swal !== 'undefined') {
            Swal.fire(opts);
        } else {
            alert(opts.text || opts.title || '');
        }
    }

    // ---- Main click handler ----
    $('#insert').on('click', function() {
        if (isProcessing) return;
        isProcessing = true;

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Memproses...');
        console.log('[ANTRIAN] Generating number...');

        var postData = {};
        if (isAndroid) {
            postData.target = 'android';
        }

        $.ajax({
            type: 'POST',
            url: '/api/nomor/insert',
            data: postData,
            dataType: 'json',
            success: function(response) {
                try {
                    console.log('[ANTRIAN] Number generated:', response.no_antrian);

                    if (response.success) {
                        $('#antrian').html(response.no_antrian).fadeIn('slow');

                        if (response.target === 'android') {
                            if (response.server_print_success) {
                                console.log('[ANTRIAN] Android: server print succeeded');
                                safeSwal({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    html: 'Nomor antrian Anda: <strong>' + response.no_antrian + '</strong><br><br>Silakan menunggu hingga nomor antrian Anda dipanggil.',
                                    confirmButtonColor: '#667eea',
                                    confirmButtonText: 'Mengerti'
                                });
                            } else {
                                console.log('[ANTRIAN] Android: server print failed, trying client-side...');
                                safeSwal({
                                    icon: 'info',
                                    title: 'Mencoba Mencetak...',
                                    html: '<strong>' + response.no_antrian + '</strong><br><br>Mencetak melalui perangkat Anda...',
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });

                                (async function() {
                                    try {
                                        var ok = await tryClientPrint(response.no_antrian);
                                        if (typeof Swal !== 'undefined') Swal.close();
                                        if (ok) {
                                            safeSwal({
                                                icon: 'success',
                                                title: 'Berhasil',
                                                html: 'Nomor antrian Anda: <strong>' + response.no_antrian + '</strong><br><br>Silakan menunggu hingga nomor antrian Anda dipanggil.',
                                                confirmButtonColor: '#667eea',
                                                confirmButtonText: 'Mengerti'
                                            });
                                        } else {
                                            safeSwal({
                                                icon: 'warning',
                                                title: 'Antrian Berhasil Diambil',
                                                html: 'Nomor antrian Anda: <strong>' + response.no_antrian + '</strong><br><br>Printer tidak merespons. Harap hubungi petugas untuk mencetak tiket Anda.',
                                                confirmButtonColor: '#667eea',
                                                confirmButtonText: 'Mengerti'
                                            });
                                        }
                                    } catch (e) {
                                        console.error('[ANTRIAN] Android client print error:', e);
                                    }
                                })();
                            }
                        } else {
                            if (response.print_status === 'printer_error') {
                                console.log('[ANTRIAN] Print failed — number kept (requirement off)');
                                safeSwal({
                                    icon: 'warning',
                                    title: 'Antrian Berhasil Diambil',
                                    html: 'Nomor antrian Anda: <strong>' + response.no_antrian + '</strong><br><br>Pengambilan nomor berhasil, namun printer tidak merespons. Harap hubungi petugas untuk mencetak tiket Anda.',
                                    confirmButtonColor: '#667eea',
                                    confirmButtonText: 'Mengerti'
                                });
                            } else {
                                console.log('[ANTRIAN] Print success');
                                safeSwal({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    html: 'Nomor antrian Anda: <strong>' + response.no_antrian + '</strong><br><br>Silakan menunggu hingga nomor antrian Anda dipanggil.',
                                    confirmButtonColor: '#667eea',
                                    confirmButtonText: 'Mengerti'
                                });
                            }
                        }
                    } else {
                        console.log('[ANTRIAN] Print failed — deleting number...');
                        console.log('[ANTRIAN] Number deleted');
                        safeSwal({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan saat mengambil nomor antrian.',
                            confirmButtonColor: '#667eea'
                        });
                    }
                } catch (e) {
                    console.error('[ANTRIAN] Error in success handler:', e);
                }

                loadAntrian();
                console.log('[ANTRIAN] Display updated');
                isProcessing = false;
                btn.prop('disabled', false).html('<i class="bi-person-plus me-2"></i> Ambil Nomor');
            },
            error: function(jqXHR) {
                try {
                    loadAntrian();
                    var msg = 'Terjadi kesalahan pada sistem. Silakan coba lagi atau hubungi IT Support.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        msg = jqXHR.responseJSON.message;
                    }
                    console.log('[ANTRIAN] System error — display updated');
                    safeSwal({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: msg,
                        confirmButtonColor: '#667eea'
                    });
                } catch (e) {
                    console.error('[ANTRIAN] Error in error handler:', e);
                }

                isProcessing = false;
                btn.prop('disabled', false).html('<i class="bi-person-plus me-2"></i> Ambil Nomor');
            }
        });
    });
});
JS;
require __DIR__ . '/../../layouts/main.php';
?>
