<?php

namespace App\Core;

class App
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function get(string $path, callable $handler): void
    {
        $this->router->get($path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->router->post($path, $handler);
    }

    public function any(string $path, callable $handler): void
    {
        $this->router->any($path, $handler);
    }

    public function run(): void
    {
        $this->checkScheduledReset();
        $this->router->dispatch();
    }

    private function checkScheduledReset(): void
    {
        $resetTime = getenv('RESET_TIME');
        if ($resetTime === false || $resetTime === '' || strtolower($resetTime) === 'false') {
            return;
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $resetTime)) {
            return;
        }

        $now = date('H:i');

        if ($now < $resetTime) {
            return;
        }

        $markerFile = BASE_PATH . '/storage/reset_marker.txt';
        $today = date('Y-m-d');
        if (file_exists($markerFile) && file_get_contents($markerFile) === $today) {
            return;
        }

        try {
            $panggilan = new \App\Models\Panggilan();
            $queue = new \App\Models\Queue();
            $panggilan->reset();
            $queue->resetDaily();
            file_put_contents($markerFile, $today);
        } catch (\Exception $e) {
            error_log('Scheduled reset failed: ' . $e->getMessage());
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
