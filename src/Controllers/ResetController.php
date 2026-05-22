<?php

namespace App\Controllers;

use App\Models\Queue;
use App\Models\Panggilan;

class ResetController
{
    private Queue $queue;
    private Panggilan $panggilan;

    public function __construct()
    {
        $this->queue = new Queue();
        $this->panggilan = new Panggilan();
    }

    public function reset(): void
    {
        if (!isset($_SESSION['username'])) {
            jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki akses untuk melakukan tindakan ini.'], 401);
            return;
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            jsonResponse(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $this->panggilan->reset();
        $this->queue->resetDaily();

        $markerFile = BASE_PATH . '/storage/reset_marker.txt';
        file_put_contents($markerFile, date('Y-m-d'));

        jsonResponse(['success' => true, 'message' => 'Antrian berhasil direset.']);
    }
}
