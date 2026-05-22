<?php

namespace App\Controllers;

use App\Models\Queue;
use App\Models\Setting;
use App\Services\PrinterService;

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
        $number = $this->queue->getNextNumber($tanggal);
        echo $number;
    }

    public function insert(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan.'], 405);
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateFile = sys_get_temp_dir() . '/rate_' . md5($ip);
        $lastTime = @file_get_contents($rateFile);
        if ($lastTime && (time() - (int)$lastTime) < 3) {
            jsonResponse(['success' => false, 'message' => 'Silakan tunggu beberapa saat sebelum mengambil nomor antrian baru.'], 429);
            return;
        }
        file_put_contents($rateFile, time());

        $printerRequired = filter_var(getenv('PRINTER_REQUIREMENT') ?: 'false', FILTER_VALIDATE_BOOLEAN);
        $tanggal = getToday();
        $result = $this->queue->create($tanggal);
        $printSuccess = PrinterService::print($result['no_antrian']);

        if ($printSuccess) {
            jsonResponse([
                'success' => true,
                'no_antrian' => $result['no_antrian'],
                'message' => 'Nomor antrian berhasil diambil.',
                'print_status' => 'printed',
                'printer_requirement' => $printerRequired,
            ]);
        } elseif ($printerRequired) {
            $this->queue->deleteById($result['id']);
            jsonResponse([
                'success' => false,
                'message' => 'Gagal mencetak tiket. Silahkan coba lagi atau hubungi petugas.',
                'printer_requirement' => $printerRequired,
            ]);
        } else {
            jsonResponse([
                'success' => true,
                'no_antrian' => $result['no_antrian'],
                'message' => 'Nomor antrian berhasil diambil, namun printer tidak merespons. Harap hubungi petugas untuk mencetak tiket Anda.',
                'print_status' => 'printer_error',
                'printer_requirement' => $printerRequired,
            ]);
        }
    }
}
