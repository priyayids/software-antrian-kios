<?php

namespace App\Controllers;

use App\Models\Queue;
use App\Models\Panggilan;
use App\Models\Setting;

class PanggilanController
{
    private Queue $queue;
    private Panggilan $panggilan;
    private Setting $setting;

    public function __construct()
    {
        $this->queue = new Queue();
        $this->panggilan = new Panggilan();
        $this->setting = new Setting();
    }

    public function index(): void
    {
        $settings = $this->setting->get();
        view('pages.panggilan.index', [
            'title' => 'Panggilan Antrian',
            'settings' => $settings,
        ]);
    }

    public function getAntrian(): void
    {
        $tanggal = getToday();
        $data = $this->queue->getAllToday($tanggal);

        if (empty($data)) {
            $data = [['id' => '', 'no_antrian' => '-', 'status' => '']];
        }

        jsonResponse(['data' => $data]);
    }

    public function getAntrianSekarang(): void
    {
        $tanggal = getToday();
        $number = $this->queue->getCurrentServing($tanggal);
        echo $number ?? '-';
    }

    public function getAntrianSelanjutnya(): void
    {
        $tanggal = getToday();
        $number = $this->queue->getNextQueue($tanggal);
        echo $number ?? '-';
    }

    public function getJumlahAntrian(): void
    {
        $tanggal = getToday();
        echo $this->queue->getCount($tanggal);
    }

    public function getSisaAntrian(): void
    {
        $tanggal = getToday();
        echo number_format($this->queue->getRemainingCount($tanggal), 0, '', '.');
    }

    public function createPanggilan(): void
    {
        $antrian = sanitize($_POST['antrian'] ?? '');
        $loket = sanitize($_POST['loket'] ?? '');

        if (empty($antrian) || empty($loket)) {
            jsonResponse(['success' => false, 'message' => 'Data tidak lengkap'], 400);
            return;
        }

        $result = $this->panggilan->create($antrian, $loket);

        if ($result) {
            jsonResponse([
                'success' => true,
                'message' => "Success create untuk panggilan {$antrian}",
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal membuat panggilan'], 500);
        }
    }

    public function update(): void
    {
        $id = (int)sanitize($_POST['id'] ?? '0');

        if ($id <= 0) {
            jsonResponse(['error' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->queue->markAsServed($id);

        if ($result) {
            jsonResponse(['success' => true]);
        } else {
            jsonResponse(['success' => false], 500);
        }
    }
}
