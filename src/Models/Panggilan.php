<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Panggilan
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $antrian, string $loket): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO queue_penggilan_antrian (antrian, loket) VALUES (:antrian, :loket)"
        );
        return $stmt->execute([
            'antrian' => $antrian,
            'loket' => $loket,
        ]);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, antrian, loket FROM queue_penggilan_antrian WHERE deleted = 0 ORDER BY id ASC LIMIT 20");
        return $stmt->fetchAll();
    }

    public function reset(): bool
    {
        $stmt = $this->db->exec("UPDATE queue_penggilan_antrian SET deleted = 1");
        return $stmt !== false;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE queue_penggilan_antrian SET deleted = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
