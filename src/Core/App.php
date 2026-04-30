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
        $this->router->dispatch();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
