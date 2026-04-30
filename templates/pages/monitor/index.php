<?php
ob_start();
$settingsData = $settings ?? [];
$warnaBackground = $settingsData['warna_background'] ?? '#1a1a2e';
$warnaPrimary = $settingsData['warna_primary'] ?? '#16213e';
$warnaText = $settingsData['warna_text'] ?? '#ffffff';
$logoPath = !empty($settingsData['logo']) && file_exists(__DIR__ . '/../../public/storage/uploads/' . $settingsData['logo'])
    ? asset('storage/uploads/' . $settingsData['logo'])
    : asset('storage/uploads/NISCAYA LOGO.png');
$youtubeId = $settingsData['youtube_id'] ?? 'Srr5BCta8UY';
$runningText = $settingsData['running_text'] ?? 'SELAMAT DATANG';
$namaInstansi = $settingsData['nama_instansi'] ?? 'ANTRIAN NISCAYA';
$alamat = $settingsData['alamat'] ?? '';
$telpon = $settingsData['telpon'] ?? '';
$email = $settingsData['email'] ?? '';
?>

<div class="monitor-wrapper" style="background-color: <?= htmlspecialchars($warnaBackground) ?>;">
    <header class="monitor-header" style="background-color: <?= htmlspecialchars($warnaPrimary) ?>;">
        <div class="d-flex align-items-center gap-3">
            <i class="bi-display-fill" style="color: <?= htmlspecialchars($warnaText) ?>; font-size: 1.5rem;"></i>
            <span class="fs-4 fw-bold" style="color: <?= htmlspecialchars($warnaText) ?>">Monitor Antrian Pendaftaran</span>
        </div>
        <div class="d-flex align-items-center gap-4" style="color: <?= htmlspecialchars($warnaText) ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bi-calendar3"></i>
                <span id="date"><?= hariIndo(date('l')) . ' ' . ($hariIni->format('d F Y') ?? '') ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi-clock"></i>
                <span id="time"></span>
            </div>
        </div>
    </header>

    <main class="monitor-main">
        <div class="container-fluid px-4">
            <div class="card monitor-info-card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo" style="max-width: 80px;">
                        </div>
                        <div class="col text-center">
                            <h4 class="fw-bold mb-1"><?= htmlspecialchars($namaInstansi) ?></h4>
                            <h6 class="mb-1"><?= htmlspecialchars($alamat) ?></h6>
                            <p class="mb-0">Tlp. <?= htmlspecialchars($telpon) ?>, Email. <?= htmlspecialchars($email) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="video-container rounded-3 overflow-hidden shadow-lg">
                        <iframe width="100%" height="450" src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?rel=0&modestbranding=1&autohide=1&showinfo=0&controls=1&loop=1&autoplay=1&playlist=<?= htmlspecialchars($youtubeId) ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="queue-display-section h-100 d-flex flex-column gap-3">
                        <div class="card queue-card queue-card-now bg-gradient-primary text-white">
                            <div class="card-header border-0 py-3">
                                <h5 class="mb-0 fw-bold">NOMOR ANTRIAN SEKARANG</h5>
                            </div>
                            <div class="card-body py-4">
                                <h1 id="antrian-sekarang" class="text-center fw-bolder animate-number">-</h1>
                            </div>
                            <div class="card-footer border-0 py-3 text-center">
                                <h5 class="mb-0 fw-bold namaLoketMonitor">-</h5>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="card queue-card queue-card-next bg-gradient-warning text-white">
                                    <div class="card-header border-0 py-2">
                                        <h6 class="mb-0 fw-bold">ANTRIAN SELANJUTNYA</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <h1 id="antrian-selanjutnya" class="text-center fw-bolder">-</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card queue-card queue-card-total bg-gradient-info text-white">
                                    <div class="card-header border-0 py-2">
                                        <h6 class="mb-0 fw-bold">TOTAL ANTRIAN</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <h1 id="jumlah-antrian" class="text-center fw-bolder">-</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="monitor-footer" style="background-color: <?= htmlspecialchars($warnaPrimary) ?>; color: <?= htmlspecialchars($warnaText) ?>;">
        <div class="marquee-container">
            <marquee behavior="scroll" direction="left"><b><?= htmlspecialchars($runningText) ?></b></marquee>
        </div>
        <div class="text-center small">
            copyright &copy; <?= date('Y') ?> by Niscaya
        </div>
    </footer>
</div>

<audio id="tingtung" src="<?= asset('assets/audio/tingtung.mp3') ?>"></audio>

<?php
$content = ob_get_clean();
$inlineScript = <<<JS
$(document).ready(function() {
    var bell = document.getElementById('tingtung');
    var queuePanggil = [];
    var isPlay = false;

    const checkQueuePanggil = (key, arrayOfQueue) => {
        return arrayOfQueue.some(q => q.id === key);
    };

    const get_panggilan = () => {
        $.ajax({
            url: '/api/monitor/panggilan',
            method: 'POST',
            async: true,
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result.success && result.data.length > 0) {
                    var newQueues = [];
                    result.data.forEach(function(element) {
                        if (!checkQueuePanggil(element.id, queuePanggil)) {
                            queuePanggil.push(element);
                            newQueues.push(element);
                        }
                    });
                    if (newQueues.length > 0 && !isPlay) {
                        panggilAntrian();
                    }
                }
            }
        });
    };

    const delete_panggilan = (id) => {
        $.ajax({
            url: '/api/monitor/panggilan/delete',
            method: 'POST',
            data: { id: id },
            async: true,
            cache: false,
            dataType: 'json'
        });
    };

    function loadStats() {
        $('#jumlah-antrian').load('/api/panggilan/jumlah');
        $('#antrian-selanjutnya').load('/api/panggilan/selanjutnya');

        $.get('/api/panggilan/sekarang', function(data) {
            if (data && data !== '-') {
                $('#antrian-sekarang').fadeOut(400, function() {
                    $(this).text(data).fadeIn(400);
                });
            }
        });
    }

    loadStats();
    get_panggilan();

    setInterval(function() {
        loadStats();
        get_panggilan();
    }, 1000);

    function panggilAntrian() {
        if (queuePanggil.length === 0) return;

        var value = queuePanggil[0];
        isPlay = true;

        $("#antrian-sekarang").fadeOut(300, function() {
            $(this).text(value.antrian).fadeIn(300);
        });
        $(".namaLoketMonitor").text("LOKET " + value.loket);

        bell.currentTime = 0;
        
        var playPromise = bell.play();
        if (playPromise !== undefined) {
            playPromise.then(function() {
                // Audio started successfully
            }).catch(function(error) {
                console.log("Bell play failed:", error);
            });
        }

        var durasi_bell = (bell.duration || 0.5) * 1000;

        setTimeout(function() {
            if (typeof responsiveVoice !== 'undefined') {
                responsiveVoice.speak("Nomor Antrian, " + value.antrian + ", menuju, loket, " + value.loket, "Indonesian Female", {
                    rate: 0.9,
                    pitch: 1,
                    volume: 1,
                    onend: function() {
                        queuePanggil.shift();
                        isPlay = false;
                        delete_panggilan(value.id);
                        if (queuePanggil.length > 0) panggilAntrian();
                    }
                });
            } else {
                console.log("responsiveVoice not loaded");
                queuePanggil.shift();
                isPlay = false;
                delete_panggilan(value.id);
                if (queuePanggil.length > 0) panggilAntrian();
            }
        }, durasi_bell);
    }

    function jam() {
        var e = document.getElementById("time"),
            d = new Date(),
            h = d.getHours(),
            m = d.getMinutes(),
            s = d.getSeconds();
        m = m < 10 ? "0" + m : m;
        s = s < 10 ? "0" + s : s;
        e.innerHTML = h + ":" + m + ":" + s;
        setTimeout(jam, 1000);
    }
    jam();
});
JS;
require __DIR__ . '/../../layouts/monitor.php';
?>
