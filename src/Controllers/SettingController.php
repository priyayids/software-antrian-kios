<?php

namespace App\Controllers;

use App\Models\Setting;

class SettingController
{
    private Setting $setting;

    public function __construct()
    {
        $this->setting = new Setting();
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $loginError = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $settings = $this->setting->get();

        view('pages.setting.index', [
            'title' => 'Setting Aplikasi',
            'settings' => $settings,
            'isLoggedIn' => isset($_SESSION['username']),
            'loginError' => $loginError,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['login_error']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('setting'));
            return;
        }

        $usernameInput = sanitize($_POST['username'] ?? '');
        $passwordInput = $_POST['password'] ?? '';

        $adminUsername = getenv('ADMIN_USERNAME') ?: 'superadmin';
        $adminPassword = getenv('ADMIN_PASSWORD') ?: 'superadmin@123';

        if (!empty($usernameInput) && !empty($passwordInput)) {
            if ($usernameInput === $adminUsername && $passwordInput === $adminPassword) {
                $_SESSION['username'] = $usernameInput;
                redirect(url('setting'));
            } else {
                $_SESSION['login_error'] = 'Username atau password salah';
                redirect(url('setting'));
            }
        } else {
            $_SESSION['login_error'] = 'Username dan password tidak boleh kosong';
            redirect(url('setting'));
        }
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();
        jsonResponse(['success' => true]);
    }

    public function save(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['username'])) {
            jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki akses untuk melakukan tindakan ini.'], 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan.'], 405);
            return;
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            jsonResponse(['success' => false, 'message' => 'Token CSRF tidak valid. Silakan muat ulang halaman.'], 403);
            return;
        }

        $data = [
            'id' => $_POST['id'] ?? '',
            'nama_instansi' => sanitizeForStorage($_POST['nama_instansi'] ?? ''),
            'alamat' => sanitizeForStorage($_POST['alamat'] ?? ''),
            'telpon' => sanitizeForStorage($_POST['telpon'] ?? ''),
            'email' => sanitizeForStorage($_POST['email'] ?? ''),
            'running_text' => sanitizeForStorage($_POST['running_text'] ?? ''),
            'youtube_id' => sanitizeForStorage($_POST['youtube_id'] ?? ''),
            'warna_primary' => sanitizeForStorage($_POST['warna_primary'] ?? ''),
            'warna_secondary' => sanitizeForStorage($_POST['warna_secondary'] ?? ''),
            'warna_accent' => sanitizeForStorage($_POST['warna_accent'] ?? ''),
            'warna_background' => sanitizeForStorage($_POST['warna_background'] ?? ''),
            'warna_text' => sanitizeForStorage($_POST['warna_text'] ?? ''),
        ];

        $colorDefaults = [
            'warna_primary' => '#00923f',
            'warna_secondary' => '#1dedae',
            'warna_accent' => '#6083a9',
            'warna_background' => '#5dee9c',
            'warna_text' => '#ffffff',
        ];
        foreach ($colorDefaults as $key => $default) {
            if (empty($data[$key])) {
                $data[$key] = $default;
            }
        }

        $noLoket = $_POST['no_loket'] ?? [];
        $namaLoket = $_POST['nama_loket'] ?? [];

        $loket = [];
        if (count($noLoket) > 0) {
            foreach ($noLoket as $key => $val) {
                $loket[] = [
                    'no_loket' => sanitize($val),
                    'nama_loket' => sanitize($namaLoket[$key] ?? ''),
                ];
            }
        }

        $data['list_loket'] = json_encode($loket);

        $logo = sanitizeForStorage($_POST['nama_logo'] ?? '');

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== 4) {
            $targetDirectory = BASE_PATH . '/public/storage/uploads/';
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            $fileName = basename($_FILES['logo']['name']);
            $targetFile = $targetDirectory . $fileName;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            if ($_FILES['logo']['size'] > 2000000) {
                jsonResponse(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.']);
                return;
            }

            if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                jsonResponse(['success' => false, 'message' => 'Format file tidak didukung. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.']);
                return;
            }

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
                $logo = $fileName;
            } else {
                jsonResponse(['success' => false, 'message' => 'Gagal mengunggah file. Pastikan folder upload memiliki izin yang benar.']);
                return;
            }
        }

        $data['logo'] = $logo;

        try {
            $result = $this->setting->save($data);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Pengaturan berhasil disimpan.', 'filename' => $logo]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Gagal menyimpan pengaturan. Terjadi kesalahan pada database.']);
            }
        } catch (\Exception $e) {
            jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan pada sistem: ' . $e->getMessage()], 500);
        }
    }
}
