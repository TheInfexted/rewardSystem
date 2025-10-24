<?php

namespace App\Models;

use CodeIgniter\Model;

class WheelItemsModel extends Model
{
    protected $table = 'wheel_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = [
        'item_name',
        'item_prize',
        'item_types',
        'winning_rate',
        'order',
        'is_active',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active wheel items ordered by order field
     */
    public function getActiveItems()
    {
        return $this->where('is_active', 1)
                    ->orderBy('order', 'ASC')
                    ->findAll();
    }

    /**
     * Get exactly 8 items for the wheel (fill with empty if less)
     */
    public function getWheelItems()
    {
        $items = $this->getActiveItems();
        
        // Ensure we have exactly 8 items
        while (count($items) < 8) {
            $items[] = [
                'item_id' => 0,
                'item_name' => '空',
                'item_prize' => 0,
                'item_types' => 'product',
                'winning_rate' => 0,
                'order' => count($items) + 1
            ];
        }
        
        // Take only first 8 if more than 8
        return array_slice($items, 0, 8);
    }

    /**
     * Calculate winning item based on rates
     */
    public function calculateWinner($items)
    {
        // Filter items with winning_rate > 0
        $winnableItems = [];
        $winnableKeys = [];
        
        foreach ($items as $key => $item) {
            if (isset($item['winning_rate']) && floatval($item['winning_rate']) > 0) {
                $winnableItems[] = $item;
                $winnableKeys[] = $key;
            }
        }
        
        // If no winnable items, return first item
        if (empty($winnableItems)) {
            return 0;
        }
        
        $totalRate = 0;
        $ranges = [];
        
        // Calculate ranges for winnable items only
        foreach ($winnableItems as $index => $item) {
            $ranges[$index] = [
                'start' => $totalRate,
                'end' => $totalRate + floatval($item['winning_rate']),
                'original_key' => $winnableKeys[$index]
            ];
            $totalRate += floatval($item['winning_rate']);
        }
        
        // Generate random number
        $random = mt_rand(0, $totalRate * 100) / 100;
        
        // Find winner
        foreach ($ranges as $range) {
            if ($random >= $range['start'] && $random < $range['end']) {
                return $range['original_key'];
            }
        }
        
        // Default to first winnable item if no match
        return $winnableKeys[0] ?? 0;
    }
    
    public function testDirectUpdate($id)
    {
        $db = \Config\Database::connect();
        
        try {
            // First, check if the record exists
            $query = $db->query("SELECT * FROM wheel_items WHERE item_id = ?", [$id]);
            $item = $query->getRow();
            
            if (!$item) {
                echo "Item with ID $id not found";
                return;
            }
            
            echo "Current item: " . json_encode($item) . "<br><br>";
            
            // Try direct SQL update
            $newName = "Direct SQL Test - " . date('Y-m-d H:i:s');
            $sql = "UPDATE wheel_items SET item_name = ?, updated_at = NOW() WHERE item_id = ?";
            $result = $db->query($sql, [$newName, $id]);
            
            if ($result) {
                echo "Direct SQL update successful<br>";
                
                // Verify the update
                $query = $db->query("SELECT * FROM wheel_items WHERE item_id = ?", [$id]);
                $updatedItem = $query->getRow();
                echo "Updated item: " . json_encode($updatedItem) . "<br>";
            } else {
                echo "Direct SQL update failed<br>";
                echo "Error: " . $db->error() . "<br>";
            }
            
        } catch (\Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }
}