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

        $settings = $this->setting->get();

        view('pages.setting.index', [
            'title' => 'Setting Aplikasi',
            'settings' => $settings,
            'isLoggedIn' => isset($_SESSION['username']),
        ]);
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
        echo "Success";
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

        $data = sanitizeArray($_POST);

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

        $logo = $data['nama_logo'] ?? '';

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== 4) {
            $targetDirectory = __DIR__ . '/../../public/storage/uploads/';
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
                jsonResponse(['success' => true, 'message' => 'Pengaturan berhasil disimpan.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Gagal menyimpan pengaturan. Terjadi kesalahan pada database.']);
            }
        } catch (\Exception $e) {
            jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan pada sistem: ' . $e->getMessage()], 500);
        }
    }
}
