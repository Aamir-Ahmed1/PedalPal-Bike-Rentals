<?php

declare(strict_types=1);

namespace PedalPal\Controllers;

use PedalPal\Core\Database;
use PedalPal\Core\Response;
use PDO;

final class AccessoryController
{
    private PDO $db;

    private const BUNDLE_IDS = [1, 3];
    private const BUNDLE_DISCOUNT = 0.10;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function index(array $params): array
    {
        $bikeType = $params['bikeType'] ?? $_GET['bikeType'] ?? '';

        if ($bikeType !== '') {
            $stmt = $this->db->prepare('SELECT * FROM accessories WHERE compatible_with LIKE ? OR compatible_with LIKE ?');
            $stmt->execute(["%\"{$bikeType}\"%", '%"all"%']);
        } else {
            $stmt = $this->db->query('SELECT * FROM accessories ORDER BY id');
        }

        $accessories = $stmt->fetchAll();

        return array_map(function ($acc) {
            $acc['compatible_with'] = json_decode($acc['compatible_with'] ?? '[]', true) ?? [];
            return $acc;
        }, $accessories);
    }

    public function order(): array
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($input) || !is_array($input)) {
            Response::error('Invalid order data', 400);
        }

        $this->db->beginTransaction();

        try {
            $totalPrice = 0.0;
            $orderItems = [];
            $orderedIds = [];
            $hasBundleA = false;
            $hasBundleB = false;

            foreach ($input as $item) {
                $id = (int)($item['AccessoryID'] ?? 0);
                $qty = (int)($item['Quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $stmt = $this->db->prepare('SELECT * FROM accessories WHERE id = ?');
                $stmt->execute([$id]);
                $acc = $stmt->fetch();

                if (!$acc) {
                    Response::error("Accessory ID {$id} not found", 404);
                }

                if ($acc['stock_count'] < $qty) {
                    Response::error("Insufficient stock for {$acc['name']}", 409);
                }

                $lineTotal = round($acc['unit_price'] * $qty, 2);
                $totalPrice += $lineTotal;
                $orderItems[] = ['accessory' => $acc, 'quantity' => $qty, 'lineTotal' => $lineTotal];
                $orderedIds[] = $id;

                if ($id === self::BUNDLE_IDS[0]) {
                    $hasBundleA = true;
                }
                if ($id === self::BUNDLE_IDS[1]) {
                    $hasBundleB = true;
                }
            }

            if (empty($orderItems)) {
                Response::error('No valid items in order', 400);
            }

            $bundleApplied = $hasBundleA && $hasBundleB;
            $discount = $bundleApplied ? round($totalPrice * self::BUNDLE_DISCOUNT, 2) : 0.0;
            $finalTotal = round($totalPrice - $discount, 2);

            $stmt = $this->db->prepare('INSERT INTO orders (total_amount, discount_amount, bundle_discount_applied) VALUES (?, ?, ?)');
            $stmt->execute([$finalTotal, $discount, $bundleApplied ? 1 : 0]);
            $orderId = (int)$this->db->lastInsertId();

            foreach ($orderItems as $item) {
                $acc = $item['accessory'];
                $qty = $item['quantity'];

                $this->db->prepare('UPDATE accessories SET stock_count = stock_count - ? WHERE id = ?')
                    ->execute([$qty, $acc['id']]);

                $this->db->prepare('INSERT INTO order_items (order_id, accessory_id, quantity, unit_price) VALUES (?, ?, ?, ?)')
                    ->execute([$orderId, $acc['id'], $qty, $acc['unit_price']]);
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => $bundleApplied
                    ? 'Order placed! Bundle deal applied: 10% off for Water Bottle + Bike Light.'
                    : 'Order placed successfully.',
                'totalPrice' => $finalTotal,
                'discountAmount' => $discount,
                'bundleDiscountApplied' => $bundleApplied,
                'orderId' => $orderId,
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            Response::error('Order processing failed: ' . $e->getMessage(), 500);
        }
    }
}
