<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Queue
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $tanggal): array
    {
        $noAntrian = generateQueueNumber($this->db, $tanggal);

        $stmt = $this->db->prepare(
            "INSERT INTO queue_antrian_admisi (tanggal, no_antrian) VALUES (:tanggal, :no_antrian)"
        );
        $stmt->execute([
            'tanggal' => $tanggal,
            'no_antrian' => $noAntrian,
        ]);

        return [
            'id' => (int)$this->db->lastInsertId(),
            'no_antrian' => $noAntrian,
            'tanggal' => $tanggal,
        ];
    }

    public function getLatestNumber(string $tanggal): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT MAX(no_antrian) as jumlah FROM queue_antrian_admisi WHERE tanggal = :tanggal"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return $row['jumlah'] ?? null;
    }

    public function getCount(string $tanggal): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(id) as jumlah FROM queue_antrian_admisi WHERE tanggal = :tanggal"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return (int)($row['jumlah'] ?? 0);
    }

    public function getRemainingCount(string $tanggal): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(id) as jumlah FROM queue_antrian_admisi WHERE tanggal = :tanggal AND status = '0'"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return (int)($row['jumlah'] ?? 0);
    }

    public function getCurrentServing(string $tanggal): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT no_antrian FROM queue_antrian_admisi WHERE tanggal = :tanggal AND status = '1' ORDER BY updated_date DESC LIMIT 1"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return $row['no_antrian'] ?? null;
    }

    public function getNextQueue(string $tanggal): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT no_antrian FROM queue_antrian_admisi WHERE tanggal = :tanggal AND status = '0' ORDER BY no_antrian ASC LIMIT 1"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return $row['no_antrian'] ?? null;
    }

    public function getAllToday(string $tanggal): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, no_antrian, status FROM queue_antrian_admisi WHERE tanggal = :tanggal ORDER BY id ASC"
        );
        $stmt->execute(['tanggal' => $tanggal]);

        return $stmt->fetchAll();
    }

    public function markAsServed(int $id): bool
    {
        $updatedDate = gmdate('Y-m-d H:i:s', time() + 60 * 60 * 7);

        $stmt = $this->db->prepare(
            "UPDATE queue_antrian_admisi SET status = '1', updated_date = :updated_date WHERE id = :id"
        );
        return $stmt->execute([
            'id' => $id,
            'updated_date' => $updatedDate,
        ]);
    }
}
