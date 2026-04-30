<?php

namespace App\Controllers;

use App\Models\Queue;
use App\Models\Panggilan;
use App\Models\Setting;

class MonitorController
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
        $hariIni = new \DateTime();

        view('pages.monitor.index', [
            'title' => 'Monitor Antrian',
            'settings' => $settings,
            'hariIni' => $hariIni,
        ]);
    }

    public function getPanggilan(): void
    {
        $data = $this->panggilan->getAll();

        jsonResponse([
            'success' => true,
            'message' => 'Success',
            'data' => $data,
        ]);
    }

    public function deletePanggilan(): void
    {
        $id = (int)sanitize($_POST['id'] ?? '0');

        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->panggilan->delete($id);

        if ($result) {
            jsonResponse(['success' => true, 'message' => "Delete Success on id {$id}"]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Error'], 500);
        }
    }
}
