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
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT MAX(no_antrian) as nomor FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0 FOR UPDATE"
            );
            $stmt->execute(['tanggal' => $tanggal]);
            $row = $stmt->fetch();

            $noAntrian = ($row && $row['nomor']) ? sprintf("%03d", (int)$row['nomor'] + 1) : '001';

            $stmt = $this->db->prepare(
                "INSERT INTO queue_antrian_admisi (tanggal, no_antrian) VALUES (:tanggal, :no_antrian)"
            );
            $stmt->execute([
                'tanggal' => $tanggal,
                'no_antrian' => $noAntrian,
            ]);

            $this->db->commit();

            return [
                'id' => (int)$this->db->lastInsertId(),
                'no_antrian' => $noAntrian,
                'tanggal' => $tanggal,
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getLatestNumber(string $tanggal): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT MAX(no_antrian) as jumlah FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return $row['jumlah'] ?? null;
    }

    public function getNextNumber(string $tanggal): string
    {
        $stmt = $this->db->prepare(
            "SELECT MAX(no_antrian) as nomor FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        if ($row && $row['nomor']) {
            return sprintf("%03d", (int)$row['nomor'] + 1);
        }
        return '001';
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE queue_antrian_admisi SET deleted = 1 WHERE id = :id"
        );
        return $stmt->execute(['id' => $id]);
    }

    public function getCount(string $tanggal): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(id) as jumlah FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return (int)($row['jumlah'] ?? 0);
    }

    public function getRemainingCount(string $tanggal): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(id) as jumlah FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0 AND status = '0'"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return (int)($row['jumlah'] ?? 0);
    }

    public function getCurrentServing(string $tanggal): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT no_antrian FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0 AND status = '1' ORDER BY updated_date IS NULL, updated_date DESC LIMIT 1"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return $row['no_antrian'] ?? null;
    }

    public function getNextQueue(string $tanggal): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT no_antrian FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0 AND status = '0' ORDER BY no_antrian ASC LIMIT 1"
        );
        $stmt->execute(['tanggal' => $tanggal]);
        $row = $stmt->fetch();

        return $row['no_antrian'] ?? null;
    }

    public function resetDaily(): int
    {
        $stmt = $this->db->exec("UPDATE queue_antrian_admisi SET deleted = 1");
        return $stmt;
    }

    public function getAllToday(string $tanggal): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, no_antrian, status FROM queue_antrian_admisi WHERE tanggal = :tanggal AND deleted = 0 ORDER BY id ASC"
        );
        $stmt->execute(['tanggal' => $tanggal]);

        return $stmt->fetchAll();
    }

    public function markAsServed(int $id): bool
    {
        $updatedDate = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            "UPDATE queue_antrian_admisi SET status = '1', updated_date = :updated_date WHERE id = :id"
        );
        return $stmt->execute([
            'id' => $id,
            'updated_date' => $updatedDate,
        ]);
    }
}
