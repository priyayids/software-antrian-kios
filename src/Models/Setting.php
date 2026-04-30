<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Setting
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function get(): array
    {
        $stmt = $this->db->query("SELECT * FROM queue_setting ORDER BY id DESC LIMIT 1");
        return $stmt->fetch() ?: [];
    }

    public function save(array $data): bool
    {
        $fields = [
            'nama_instansi', 'logo', 'alamat', 'telpon', 'email',
            'running_text', 'youtube_id', 'list_loket',
            'warna_primary', 'warna_secondary', 'warna_accent',
            'warna_background', 'warna_text',
        ];

        if (empty($data['id'])) {
            $placeholders = [];
            $params = [];
            foreach ($fields as $field) {
                $placeholders[] = ":{$field}";
                $params[$field] = $data[$field] ?? null;
            }

            $sql = "INSERT INTO queue_setting (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            $setParts = [];
            $params = ['id' => $data['id']];
            foreach ($fields as $field) {
                $setParts[] = "{$field} = :{$field}";
                $params[$field] = $data[$field] ?? null;
            }

            $sql = "UPDATE queue_setting SET " . implode(', ', $setParts) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
    }

    public function getLoketList(): array
    {
        $settings = $this->get();
        if (!empty($settings['list_loket'])) {
            return json_decode($settings['list_loket'], true) ?? [];
        }
        return [];
    }
}
