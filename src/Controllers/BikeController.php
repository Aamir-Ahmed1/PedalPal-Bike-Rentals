<?php

declare(strict_types=1);

namespace PedalPal\Controllers;

use PedalPal\Core\Database;
use PedalPal\Core\Response;
use PDO;

final class BikeController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function beachCruisers(): array
    {
        $stmt = $this->db->query('SELECT * FROM beach_cruisers ORDER BY id');
        return $stmt->fetchAll();
    }

    public function mountainBikes(): array
    {
        $stmt = $this->db->query('SELECT * FROM mountain_bikes ORDER BY id');
        return $stmt->fetchAll();
    }

    public function rent(array $params): array
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $bikeType = $input['bikeType'] ?? '';
        $bikeId = (int)($input['bikeId'] ?? 0);

        $table = match ($bikeType) {
            'beach' => 'beach_cruisers',
            'mountain' => 'mountain_bikes',
            default => null,
        };

        if ($table === null) {
            Response::error('Invalid bike type', 400);
        }

        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$bikeId]);
        $bike = $stmt->fetch();

        if (!$bike) {
            Response::error('Bike not found', 404);
        }

        $this->db->prepare("UPDATE {$table} SET is_available = 0 WHERE id = ?")->execute([$bikeId]);

        return ['success' => true, 'message' => 'Bike rented successfully', 'bikeId' => $bikeId];
    }

    public function reset(): array
    {
        $this->db->exec('UPDATE beach_cruisers SET is_available = 1 WHERE id IN (1,2,4,5)');
        $this->db->exec('UPDATE beach_cruisers SET is_available = 0 WHERE id IN (3,6)');
        $this->db->exec('UPDATE mountain_bikes SET is_available = 1 WHERE id IN (101,102,104,105)');
        $this->db->exec('UPDATE mountain_bikes SET is_available = 0 WHERE id IN (103,106)');
        $this->db->exec('UPDATE accessories SET stock_count = CASE id WHEN 1 THEN 15 WHEN 2 THEN 8 WHEN 3 THEN 20 WHEN 4 THEN 6 END');

        return ['success' => true, 'message' => 'All data reset to defaults'];
    }
}
