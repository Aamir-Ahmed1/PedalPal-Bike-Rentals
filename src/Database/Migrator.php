<?php

declare(strict_types=1);

namespace PedalPal\Database;

use PDO;
use PedalPal\Core\Database;

final class Migrator
{
    public static function migrate(): void
    {
        $db = Database::connect();

        $db->exec('
            CREATE TABLE IF NOT EXISTS beach_cruisers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                model_name TEXT NOT NULL,
                color TEXT NOT NULL,
                frame_size TEXT NOT NULL,
                daily_rate REAL NOT NULL,
                is_available INTEGER NOT NULL DEFAULT 1
            )
        ');

        $db->exec('
            CREATE TABLE IF NOT EXISTS mountain_bikes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                model_name TEXT NOT NULL,
                brand TEXT NOT NULL,
                gear_count INTEGER NOT NULL,
                suspension_type TEXT NOT NULL,
                frame_material TEXT NOT NULL,
                daily_rate REAL NOT NULL,
                is_available INTEGER NOT NULL DEFAULT 1,
                terrain TEXT NOT NULL,
                weight_kg REAL NOT NULL
            )
        ');

        $db->exec('
            CREATE TABLE IF NOT EXISTS accessories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                category TEXT NOT NULL,
                description TEXT NOT NULL,
                unit_price REAL NOT NULL,
                stock_count INTEGER NOT NULL DEFAULT 0,
                compatible_with TEXT NOT NULL DEFAULT \'[]\'
            )
        ');

        $db->exec('
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                bike_id INTEGER,
                bike_type TEXT,
                total_amount REAL NOT NULL DEFAULT 0,
                discount_amount REAL NOT NULL DEFAULT 0,
                bundle_discount_applied INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )
        ');

        $db->exec('
            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                accessory_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                unit_price REAL NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id)
            )
        ');

        echo "Migration completed successfully.\n";
    }

    public static function seed(): void
    {
        $db = Database::connect();

        $beachCount = $db->query('SELECT COUNT(*) FROM beach_cruisers')->fetchColumn();
        $mountainCount = $db->query('SELECT COUNT(*) FROM mountain_bikes')->fetchColumn();
        $accessoryCount = $db->query('SELECT COUNT(*) FROM accessories')->fetchColumn();

        if ($beachCount > 0 || $mountainCount > 0 || $accessoryCount > 0) {
            echo "Database already has data. Skipping seed.\n";
            return;
        }

        $beachInsert = $db->prepare('
            INSERT INTO beach_cruisers (id, model_name, color, frame_size, daily_rate, is_available)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        $beachData = [
            [1, 'Sunset Drifter', 'Coral', 'Medium', 14.99, 1],
            [2, 'Ocean Breeze', 'Teal', 'Large', 16.99, 1],
            [3, 'Sandy Shores', 'Cream', 'Small', 12.99, 0],
            [4, 'Tropical Wave', 'Lime Green', 'Medium', 15.99, 1],
            [5, 'Breezy Blue', 'Sky Blue', 'Large', 17.99, 1],
            [6, 'Flamingo Glide', 'Hot Pink', 'Small', 13.99, 0],
        ];

        foreach ($beachData as $row) {
            $beachInsert->execute($row);
        }

        $mountainInsert = $db->prepare('
            INSERT INTO mountain_bikes (id, model_name, brand, gear_count, suspension_type, frame_material, daily_rate, is_available, terrain, weight_kg)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $mountainData = [
            [101, 'TrailBlazer X9', 'ApexRide', 21, 'Full', 'Aluminum', 24.99, 1, 'All-Mountain', 13.5],
            [102, 'Summit Shredder', 'PeakForce', 27, 'Full', 'Carbon Fiber', 34.99, 1, 'Enduro', 11.2],
            [103, 'Canyon Crusher', 'TerraRide', 18, 'Hardtail', 'Steel', 19.99, 0, 'Cross-Country', 14.8],
            [104, 'Ridge Runner', 'ApexRide', 24, 'Hardtail', 'Aluminum', 22.99, 1, 'Trail', 12.9],
            [105, 'Peak Predator', 'SummitX', 30, 'Full', 'Carbon Fiber', 39.99, 1, 'Downhill', 15.3],
            [106, 'Mud Maverick', 'TerraRide', 21, 'Full', 'Aluminum', 27.99, 0, 'Enduro', 13.1],
        ];

        foreach ($mountainData as $row) {
            $mountainInsert->execute($row);
        }

        $accessoryInsert = $db->prepare('
            INSERT INTO accessories (id, name, category, description, unit_price, stock_count, compatible_with)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        $accessoryData = [
            [1, 'Trail Blazer Water Bottle', 'Hydration', 'Keeps your water cold and your excuses for stopping minimal.', 2.99, 15, json_encode(['mountain', 'beach'])],
            [2, 'Wicker Beach Basket', 'Storage', 'Holds your picnic, your sunscreen, and one very small dog.', 4.99, 8, json_encode(['beach'])],
            [3, 'NightRider Bike Light', 'Safety', 'Because riding in the dark and just hoping for the best is not a strategy.', 3.49, 20, json_encode(['mountain', 'beach'])],
            [4, 'Summit Cargo Basket', 'Storage', 'Straps to your frame. Carries snacks. Does not carry your feelings.', 5.99, 6, json_encode(['mountain'])],
        ];

        foreach ($accessoryData as $row) {
            $accessoryInsert->execute($row);
        }

        echo "Database seeded successfully.\n";
    }
}
