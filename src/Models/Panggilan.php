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
        $stmt = $this->db->query("SELECT id, antrian, loket FROM queue_penggilan_antrian ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM queue_penggilan_antrian WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
