<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\SpinHistoryModel;
use App\Models\BonusClaimModel;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $spinHistoryModel;
    protected $bonusClaimModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->spinHistoryModel = new SpinHistoryModel();
        $this->bonusClaimModel = new BonusClaimModel();
    }

    /**
     * Customer Dashboard
     */
    public function dashboard()
    {
        $session = session();
        $customerId = $session->get('customer_id');
        
        if (!$customerId) {
            return redirect()->to('/reward')->with('error', 'Please login first');
        }
        
        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            $session->destroy();
            return redirect()->to('/reward')->with('error', 'Invalid session');
        }
        
        // Get check-in data
        $db = \Config\Database::connect();
        $builder = $db->table('customer_checkins');
        
        // Check if already checked in today
        $todayCheckin = $builder->where([
            'customer_id' => $customerId,
            'checkin_date' => date('Y-m-d')
        ])->get()->getRow();
        
        // Get current streak
        $streak = $this->getCheckinStreak($customerId);
        
        // Get monthly checkins
        $monthlyCheckins = $builder->where([
            'customer_id' => $customerId,
            'MONTH(checkin_date)' => date('m'),
            'YEAR(checkin_date)' => date('Y')
        ])->countAllResults();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($customerId);
        
        $data = [
            'title' => 'Customer Dashboard',
            'username' => $customer['username'],
            'profile_background' => $customer['profile_background'] ?? 'default',
            'today_checkin' => !empty($todayCheckin),
            'checkin_streak' => $streak,
            'monthly_checkins' => $monthlyCheckins,
            'total_points' => $customer['points'] ?? 0,
            'recent_activities' => $recentActivities
        ];
        
        return view('customer/dashboard', $data);
    }

    /**
     * Handle daily check-in
     */
    public function checkin()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer/dashboard');
        }

        $session = session();
        $customerId = $session->get('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first'
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('customer_checkins');
        
        // Check if already checked in today
        $existing = $builder->where([
            'customer_id' => $customerId,
            'checkin_date' => date('Y-m-d')
        ])->get()->getRow();
        
        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You have already checked in today'
            ]);
        }
        
        // Get current streak
        $streak = $this->getCheckinStreak($customerId);
        $newStreak = $streak + 1;
        $rewardPoints = min($newStreak * 10, 70); // Max 70 points on day 7
        
        // Insert check-in record
        $builder->insert([
            'customer_id' => $customerId,
            'checkin_date' => date('Y-m-d'),
            'reward_points' => $rewardPoints,
            'streak_day' => $newStreak,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Add points to customer
        $this->customerModel->addPoints($customerId, $rewardPoints);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Check-in successful!',
            'reward_points' => $rewardPoints,
            'new_streak' => $newStreak
        ]);
    }

    /**
     * Update profile background
     */
    public function updateBackground()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer/dashboard');
        }

        $session = session();
        $customerId = $session->get('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first'
            ]);
        }
        
        $background = $this->request->getPost('background');
        $validBackgrounds = ['default', 'blue', 'purple', 'green', 'red', 'orange'];
        
        if (!in_array($background, $validBackgrounds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid background selected'
            ]);
        }
        
        $this->customerModel->update($customerId, [
            'profile_background' => $background
        ]);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Background updated successfully'
        ]);
    }

    /**
     * Get check-in streak
     */
    private function getCheckinStreak($customerId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('customer_checkins');
        
        // Get yesterday's check-in
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $yesterdayCheckin = $builder->where([
            'customer_id' => $customerId,
            'checkin_date' => $yesterday
        ])->get()->getRow();
        
        if ($yesterdayCheckin) {
            return $yesterdayCheckin->streak_day;
        }
        
        return 0;
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($customerId, $limit = 5)
    {
        $activities = [];
        
        // Get recent spins
        $spins = $this->spinHistoryModel
            ->where('customer_id', $customerId)
            ->orderBy('spin_time', 'DESC')
            ->limit(3)
            ->find();
        
        foreach ($spins as $spin) {
            $activities[] = [
                'type_color' => $spin['outcome'] === 'Try Again' ? 'danger' : 'warning',
                'icon' => 'pie-chart',
                'title' => 'Wheel Spin - ' . $spin['outcome'],
                'time_ago' => $this->timeAgo($spin['spin_time']),
                'reward' => $spin['outcome'] === 'Try Again' ? 'Lost' : 'Won'
            ];
        }
        
        // Get recent check-ins
        $db = \Config\Database::connect();
        $checkins = $db->table('customer_checkins')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->limit(2)
            ->get()
            ->getResult();
        
        foreach ($checkins as $checkin) {
            $activities[] = [
                'type_color' => 'success',
                'icon' => 'check-circle',
                'title' => 'Daily Check-in',
                'time_ago' => $this->timeAgo($checkin->created_at),
                'reward' => '+' . $checkin->reward_points
            ];
        }
        
        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b['time_ago']) - strtotime($a['time_ago']);
        });
        
        return array_slice($activities, 0, $limit);
    }

    /**
     * Convert timestamp to time ago format
     */
    private function timeAgo($timestamp)
    {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }
}