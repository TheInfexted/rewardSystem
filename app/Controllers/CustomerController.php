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
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                $session->destroy();
                return redirect()->to('/reward')->with('error', 'Invalid session');
            }
            
            // Get current week check-in data
            $weekData = $this->getCurrentWeekCheckins($customerId);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($customerId);
            
            $data = [
                'title' => 'Customer Dashboard',
                'username' => $customer['username'],
                'profile_background' => $customer['profile_background'] ?? 'default',
                'today_checkin' => $weekData['today_checkin'],
                'week_checkins' => $weekData['week_checkins'],
                'checkin_streak' => $weekData['checkin_count'],
                'weekly_progress' => $weekData['weekly_progress'],
                'current_week_start' => $weekData['week_start'],
                'current_week_end' => $weekData['week_end'],
                'total_points' => $customer['points'] ?? 0,
                'recent_activities' => $recentActivities
            ];
            
            return view('customer/dashboard', $data);
        
        } catch (\Exception $e) {
            log_message('error', 'Customer dashboard error: ' . $e->getMessage());
            return redirect()->to('/reward')->with('error', 'Unable to load dashboard');
        }
    }

    /**
     * Handle weekly check-in system
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

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('customer_checkins');
            
            // Get current week dates
            $weekDates = $this->getCurrentWeekDates();
            $today = date('Y-m-d');
            
            // Check if already checked in today
            $todayCheckin = $builder->where([
                'customer_id' => $customerId,
                'checkin_date' => $today
            ])->get()->getRow();
            
            if ($todayCheckin) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You have already checked in today'
                ]);
            }
            
            // Get current week check-ins
            $weekCheckins = $builder->where('customer_id', $customerId)
                                  ->where('checkin_date >=', $weekDates['week_start'])
                                  ->where('checkin_date <=', $weekDates['week_end'])
                                  ->countAllResults();
            
            // Calculate day of week (1=Monday, 7=Sunday)
            $dayOfWeek = date('N');
            
            // Progressive reward system for the week
            $rewardPoints = $this->calculateWeeklyReward($weekCheckins + 1, $dayOfWeek);
            
            // Insert check-in record
            $inserted = $builder->insert([
                'customer_id' => $customerId,
                'checkin_date' => $today,
                'reward_points' => $rewardPoints,
                'streak_day' => $weekCheckins + 1, // This is now "week progress"
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($inserted) {
                // Add points to customer
                $this->addPointsToCustomer($customerId, $rewardPoints);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Check-in successful!',
                    'reward_points' => $rewardPoints,
                    'week_progress' => $weekCheckins + 1,
                    'bonus_message' => $this->getCheckinBonusMessage($weekCheckins + 1)
                ]);
            } else {
                throw new \Exception('Failed to insert check-in record');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Check-in error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Check-in failed. Please try again.'
            ]);
        }
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
        $validBackgrounds = ['default', 'blue', 'purple', 'green', 'red', 'orange', 'gold'];
        
        // Debug logging
        log_message('info', 'Background change request - Customer ID: ' . $customerId . ', Background: ' . $background);
        
        if (empty($background)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No background selected'
            ]);
        }
        
        if (!in_array($background, $validBackgrounds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid background selected. Valid options: ' . implode(', ', $validBackgrounds)
            ]);
        }
        
        try {
            $updated = $this->customerModel->update($customerId, [
                'profile_background' => $background
            ]);
            
            if ($updated) {
                log_message('info', 'Background updated successfully for customer ' . $customerId . ' to ' . $background);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Background updated successfully',
                    'new_background' => $background
                ]);
            } else {
                throw new \Exception('Database update failed');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Update background error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update background: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get current week check-in data (Monday to Sunday)
     */
    private function getCurrentWeekCheckins($customerId)
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('customer_checkins');
            
            // Get current week dates (Monday to Sunday)
            $weekDates = $this->getCurrentWeekDates();
            $today = date('Y-m-d');
            
            // Check if already checked in today
            $todayCheckin = $builder->where([
                'customer_id' => $customerId,
                'checkin_date' => $today
            ])->get()->getRow();
            
            // Get all check-ins for current week
            $weekCheckins = $builder->where('customer_id', $customerId)
                                  ->where('checkin_date >=', $weekDates['week_start'])
                                  ->where('checkin_date <=', $weekDates['week_end'])
                                  ->orderBy('checkin_date', 'ASC')
                                  ->get()
                                  ->getResult();
            
            // Create weekly progress array (Monday=1 to Sunday=7)
            $weeklyProgress = [];
            for ($i = 1; $i <= 7; $i++) {
                $date = date('Y-m-d', strtotime($weekDates['week_start'] . ' +' . ($i-1) . ' days'));
                $weeklyProgress[$i] = [
                    'day' => $i,
                    'date' => $date,
                    'day_name' => date('l', strtotime($date)),
                    'day_short' => date('D', strtotime($date)),
                    'checked_in' => false,
                    'is_today' => $date === $today,
                    'is_future' => $date > $today,
                    'points' => $this->calculateWeeklyReward($i, $i)
                ];
            }
            
            // Mark checked-in days
            foreach ($weekCheckins as $checkin) {
                $dayOfWeek = date('N', strtotime($checkin->checkin_date)); // 1=Monday, 7=Sunday
                if (isset($weeklyProgress[$dayOfWeek])) {
                    $weeklyProgress[$dayOfWeek]['checked_in'] = true;
                    $weeklyProgress[$dayOfWeek]['actual_points'] = $checkin->reward_points;
                }
            }
            
            return [
                'today_checkin' => !empty($todayCheckin),
                'week_checkins' => $weekCheckins,
                'checkin_count' => count($weekCheckins),
                'weekly_progress' => $weeklyProgress,
                'week_start' => $weekDates['week_start'],
                'week_end' => $weekDates['week_end']
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Get week checkins error: ' . $e->getMessage());
            return [
                'today_checkin' => false,
                'week_checkins' => [],
                'checkin_count' => 0,
                'weekly_progress' => [],
                'week_start' => date('Y-m-d'),
                'week_end' => date('Y-m-d')
            ];
        }
    }

    /**
     * Get current week dates (Monday to Sunday)
     */
    private function getCurrentWeekDates()
    {
        $currentDay = date('N'); // 1=Monday, 7=Sunday
        
        // Calculate Monday of current week
        $monday = date('Y-m-d', strtotime('-' . ($currentDay - 1) . ' days'));
        
        // Calculate Sunday of current week  
        $sunday = date('Y-m-d', strtotime($monday . ' +6 days'));
        
        return [
            'week_start' => $monday,
            'week_end' => $sunday
        ];
    }

    /**
     * Calculate weekly reward points
     */
    private function calculateWeeklyReward($checkinNumber, $dayOfWeek)
    {
        // Progressive reward system for the week
        $baseRewards = [
            1 => 10,  // Monday: 10 points
            2 => 15,  // Tuesday: 15 points  
            3 => 20,  // Wednesday: 20 points
            4 => 25,  // Thursday: 25 points
            5 => 30,  // Friday: 30 points
            6 => 40,  // Saturday: 40 points
            7 => 50   // Sunday: 50 points (bonus day)
        ];
        
        $baseReward = $baseRewards[$dayOfWeek] ?? 10;
        
        // Bonus for consecutive check-ins
        $consecutiveBonus = 0;
        if ($checkinNumber >= 3) $consecutiveBonus += 5;  // 3+ days bonus
        if ($checkinNumber >= 5) $consecutiveBonus += 10; // 5+ days bonus  
        if ($checkinNumber >= 7) $consecutiveBonus += 20; // Perfect week bonus
        
        return $baseReward + $consecutiveBonus;
    }

    /**
     * Get check-in bonus message
     */
    private function getCheckinBonusMessage($checkinCount)
    {
        switch ($checkinCount) {
            case 1:
                return "Great start! Welcome to this week's check-in challenge!";
            case 2:
                return "Nice! You're building momentum!";
            case 3:
                return "Awesome! 3 days down - bonus points activated!";
            case 4:
                return "Fantastic! You're more than halfway there!";
            case 5:
                return "Amazing! 5-day streak bonus unlocked!";
            case 6:
                return "Incredible! Just one more day for the perfect week!";
            case 7:
                return "🎉 PERFECT WEEK! You've completed all 7 days!";
            default:
                return "Keep it up!";
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($customerId, $limit = 5)
    {
        $activities = [];
        
        try {
            // Get recent spins if SpinHistoryModel exists
            if (class_exists('App\Models\SpinHistoryModel')) {
                $spins = $this->spinHistoryModel
                    ->where('customer_id', $customerId)
                    ->orderBy('spin_time', 'DESC')
                    ->limit(3)
                    ->find();
                
                foreach ($spins as $spin) {
                    $activities[] = [
                        'type_color' => $spin['item_won'] === 'Try Again' ? 'danger' : 'warning',
                        'icon' => 'pie-chart',
                        'title' => 'Wheel Spin - ' . $spin['item_won'],
                        'time_ago' => $this->timeAgo($spin['spin_time']),
                        'reward' => $spin['item_won'] === 'Try Again' ? 'Lost' : 'Won'
                    ];
                }
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
                return strcmp($b['time_ago'], $a['time_ago']);
            });
            
            return array_slice($activities, 0, $limit);
            
        } catch (\Exception $e) {
            log_message('error', 'Get recent activities error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert timestamp to time ago format
     */
    private function timeAgo($timestamp)
    {
        try {
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
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Safe method to add points to customer (avoiding the db->raw issue)
     */
    private function addPointsToCustomer($customerId, $points)
    {
        try {
            $customer = $this->customerModel->find($customerId);
            if ($customer) {
                $newPoints = $customer['points'] + $points;
                return $this->customerModel->update($customerId, [
                    'points' => $newPoints
                ]);
            }
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Add points error: ' . $e->getMessage());
            return false;
        }
    }
}