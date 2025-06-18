<?php

namespace App\Models;

use CodeIgniter\Model;

class CheckinModel extends Model
{
    protected $table = 'user_checkins';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'checkin_date',
        'checkin_time',
        'reward_points',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get today's check-in for a user
     */
    public function getTodayCheckin($userId)
    {
        return $this->where([
            'user_id' => $userId,
            'checkin_date' => date('Y-m-d')
        ])->first();
    }

    /**
     * Get consecutive check-in streak for a user
     */
    public function getCheckinStreak($userId)
    {
        $checkins = $this->where('user_id', $userId)
                        ->orderBy('checkin_date', 'DESC')
                        ->findAll();

        if (empty($checkins)) {
            return 0;
        }

        $streak = 0;
        $currentDate = date('Y-m-d');
        
        foreach ($checkins as $checkin) {
            $checkinDate = $checkin['checkin_date'];
            
            // If this is the first check-in and it's today, count it
            if ($streak === 0 && $checkinDate === $currentDate) {
                $streak = 1;
                $currentDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
                continue;
            }
            
            // If this is the expected previous day, continue streak
            if ($checkinDate === $currentDate) {
                $streak++;
                $currentDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
            } else {
                // Streak is broken
                break;
            }
        }

        return $streak;
    }

    /**
     * Get total check-ins for current month
     */
    public function getMonthlyCheckins($userId)
    {
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');

        return $this->where('user_id', $userId)
                   ->where('checkin_date >=', $firstDayOfMonth)
                   ->where('checkin_date <=', $lastDayOfMonth)
                   ->countAllResults();
    }

    /**
     * Get check-in history for a user
     */
    public function getCheckinHistory($userId, $limit = 30)
    {
        return $this->where('user_id', $userId)
                   ->orderBy('checkin_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get total reward points earned by user
     */
    public function getTotalRewardPoints($userId)
    {
        $result = $this->selectSum('reward_points')
                      ->where('user_id', $userId)
                      ->first();
        
        return $result['reward_points'] ?? 0;
    }
}