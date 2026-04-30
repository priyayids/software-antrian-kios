<?php

namespace App\Controllers;

use App\Models\Queue;

class HomeController
{
    public function index(): void
    {
        $settings = getSettings(\App\Core\Database::getConnection());
        view('pages.home', [
            'title' => 'ANTRIAN NISCAYA',
            'settings' => $settings,
        ]);
    }
}
