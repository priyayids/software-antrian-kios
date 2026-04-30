<?php

if (!function_exists('sanitize')) {
    function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitizeArray')) {
    function sanitizeArray(array $data): array
    {
        return array_map(function ($value) {
            return is_array($value) ? sanitizeArray($value) : sanitize($value);
        }, $data);
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): void
    {
        extract($data);
        $templatePath = __DIR__ . '/../../templates/' . str_replace('.', '/', $template) . '.php';
        if (file_exists($templatePath)) {
            require_once $templatePath;
        } else {
            http_response_code(404);
            echo "View not found: {$template}";
        }
    }
}

if (!function_exists('layout')) {
    function layout(string $name, array $data = []): void
    {
        extract($data);
        $layoutPath = __DIR__ . '/../../templates/layouts/' . $name . '.php';
        if (file_exists($layoutPath)) {
            require_once $layoutPath;
        }
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(getenv('APP_URL') ?: '', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('hariIndo')) {
    function hariIndo(string $hariInggris): string
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        return $days[$hariInggris] ?? '';
    }
}

if (!function_exists('bulanIndo')) {
    function bulanIndo(string $bulan): string
    {
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        return $months[$bulan] ?? '';
    }
}

if (!function_exists('generateQueueNumber')) {
    function generateQueueNumber(\PDO $db, string $tanggal): string
    {
        $stmt = $db->prepare("SELECT MAX(no_antrian) as nomor FROM queue_antrian_admisi WHERE tanggal = :tanggal");
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        if ($row && $row['nomor']) {
            return sprintf("%03d", (int)$row['nomor'] + 1);
        }
        return '001';
    }
}

if (!function_exists('getToday')) {
    function getToday(): string
    {
        return gmdate('Y-m-d', time() + 60 * 60 * 7);
    }
}

if (!function_exists('getSettings')) {
    function getSettings(\PDO $db): array
    {
        $stmt = $db->query("SELECT * FROM queue_setting ORDER BY id DESC LIMIT 1");
        return $stmt->fetch() ?: [];
    }
}
