<?php

namespace App\Controllers;

use App\Models\Queue;
use App\Models\Setting;

class NomorController
{
    private Queue $queue;
    private Setting $setting;

    public function __construct()
    {
        $this->queue = new Queue();
        $this->setting = new Setting();
    }

    public function index(): void
    {
        $settings = $this->setting->get();
        view('pages.nomor.index', [
            'title' => 'Nomor Antrian',
            'settings' => $settings,
        ]);
    }

    public function getAntrian(): void
    {
        $tanggal = getToday();
        $number = $this->queue->getLatestNumber($tanggal);
        echo $number ?? '';
    }

    public function insert(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan.'], 405);
            return;
        }

        $tanggal = getToday();
        $result = $this->queue->create($tanggal);

        $printSuccess = true;

        try {
            $cetakFile = __DIR__ . '/../../pages/nomor/cetak.php';
            if (file_exists($cetakFile)) {
                require_once $cetakFile;
                if (function_exists('cetak')) {
                    cetak($result['no_antrian']);
                }
            }
        } catch (\Exception $e) {
            $printSuccess = false;
        }

        if ($printSuccess) {
            jsonResponse([
                'success' => true,
                'no_antrian' => $result['no_antrian'],
                'message' => 'Nomor antrian berhasil diambil.',
                'print_status' => 'printed',
            ]);
        } else {
            jsonResponse([
                'success' => true,
                'no_antrian' => $result['no_antrian'],
                'message' => 'Nomor antrian berhasil diambil, namun printer tidak merespons. Harap hubungi petugas untuk mencetak tiket Anda.',
                'print_status' => 'printer_error',
            ]);
        }
    }
}
