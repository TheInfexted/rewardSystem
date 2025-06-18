<?php

namespace App\Models;

use CodeIgniter\Model;

class SpinHistoryModel extends Model
{
    protected $table = 'spin_history';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'session_id',
        'user_ip',
        'spin_time',
        'item_won',
        'prize_amount',
        'prize_type',
        'claimed',
        'created_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    /**
     * Record a spin
     */
    public function recordSpin($sessionId, $ip, $item)
    {
        return $this->insert([
            'session_id' => $sessionId,
            'user_ip' => $ip,
            'spin_time' => date('Y-m-d H:i:s'),
            'item_won' => $item['item_name'],
            'prize_amount' => $item['item_prize'],
            'prize_type' => $item['item_types'],
            'claimed' => 0
        ]);
    }

    /**
     * Get spins count for session today
     */
    public function getSessionSpinsToday($sessionId)
    {
        return $this->where('session_id', $sessionId)
                    ->where('DATE(spin_time)', date('Y-m-d'))
                    ->countAllResults();
    }

    /**
     * Get recent wins
     */
    public function getRecentWins($limit = 10)
    {
        return $this->where('prize_amount >', 0)
                    ->orderBy('spin_time', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total_spins' => $this->countAll(),
            'total_wins' => $this->where('prize_amount >', 0)->countAllResults(),
            'total_prize_given' => $this->selectSum('prize_amount')->where('prize_type', 'cash')->first()['prize_amount'] ?? 0,
            'today_spins' => $this->where('DATE(spin_time)', date('Y-m-d'))->countAllResults(),
            'unique_players_today' => $this->select('COUNT(DISTINCT session_id) as count')
                                           ->where('DATE(spin_time)', date('Y-m-d'))
                                           ->first()['count'] ?? 0
        ];
        
        return $stats;
    }

    /**
     * Get prize distribution
     */
    public function getPrizeDistribution()
    {
        return $this->select('item_won, COUNT(*) as count, SUM(prize_amount) as total')
                    ->groupBy('item_won')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }
}