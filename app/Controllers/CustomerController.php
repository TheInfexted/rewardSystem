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
        $customerLoggedIn = $session->get('customer_logged_in');
        
        // Debug logging
        log_message('info', 'Customer Dashboard Access - Customer ID: ' . $customerId . ', Logged In: ' . ($customerLoggedIn ? 'Yes' : 'No'));
        
        if (!$customerId || !$customerLoggedIn) {
            return redirect()->to('/reward')->with('error', 'Please login first');
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                $session->remove(['customer_id', 'customer_logged_in', 'customer_data']);
                return redirect()->to('/reward')->with('error', 'Invalid session');
            }
            
            // Get current week check-in data
            $weekData = $this->getCurrentWeekCheckins($customerId);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($customerId);
            
            // Get monthly checkins count
            $monthlyCheckins = $this->getMonthlyCheckinsCount($customerId);
            
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
                'recent_activities' => $recentActivities,
                'monthly_checkins' => $monthlyCheckins
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
                $pointsAdded = $this->addPointsToCustomer($customerId, $rewardPoints);
                
                if ($pointsAdded) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Check-in successful!',
                        'points' => $rewardPoints,
                        'day' => $weekCheckins + 1
                    ]);
                } else {
                    log_message('error', 'Failed to add points after check-in');
                    return $this->response->setJSON([
                        'success' => true, // Still show success since check-in was recorded
                        'message' => 'Check-in recorded but failed to add points',
                        'points' => $rewardPoints,
                        'day' => $weekCheckins + 1
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to process check-in'
                ]);
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
     * Update customer background
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
        
        if (empty($background)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Background type is required'
            ]);
        }

        try {
            $updateData = ['profile_background' => $background];
            $updated = $this->customerModel->update($customerId, $updateData);
            
            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Background updated successfully'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update background'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Background update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Update failed. Please try again.'
            ]);
        }
    }

    /**
     * Get current week check-in data
     */
    private function getCurrentWeekCheckins($customerId)
    {
        try {
            $db = \Config\Database::connect();
            $weekDates = $this->getCurrentWeekDates();
            $today = date('Y-m-d');
            
            // Get week check-ins
            $weekCheckins = $db->table('customer_checkins')
                              ->where('customer_id', $customerId)
                              ->where('checkin_date >=', $weekDates['week_start'])
                              ->where('checkin_date <=', $weekDates['week_end'])
                              ->orderBy('checkin_date', 'ASC')
                              ->get()
                              ->getResultArray();
            
            // Check if checked in today
            $todayCheckin = false;
            foreach ($weekCheckins as $checkin) {
                if ($checkin['checkin_date'] === $today) {
                    $todayCheckin = true;
                    break;
                }
            }
            
            // Create weekly progress array (1=Monday to 7=Sunday)
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
                    'points' => $this->calculateWeeklyReward($i, $i),
                    'actual_points' => 0
                ];
            }
            
            // Mark checked-in days
            foreach ($weekCheckins as $checkin) {
                $dayOfWeek = date('N', strtotime($checkin['checkin_date'])); // 1=Monday, 7=Sunday
                if (isset($weeklyProgress[$dayOfWeek])) {
                    $weeklyProgress[$dayOfWeek]['checked_in'] = true;
                    $weeklyProgress[$dayOfWeek]['actual_points'] = $checkin['reward_points'];
                }
            }
            
            return [
                'today_checkin' => $todayCheckin,
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
        $today = new \DateTime();
        $dayOfWeek = $today->format('N'); // 1 (Monday) to 7 (Sunday)
        
        // Calculate Monday of current week
        $mondayOffset = $dayOfWeek - 1;
        $monday = clone $today;
        $monday->sub(new \DateInterval("P{$mondayOffset}D"));
        
        // Calculate Sunday of current week
        $sunday = clone $monday;
        $sunday->add(new \DateInterval('P6D'));
        
        return [
            'week_start' => $monday->format('Y-m-d'),
            'week_end' => $sunday->format('Y-m-d')
        ];
    }

    /**
     * Calculate weekly reward points
     */
    private function calculateWeeklyReward($dayNumber, $dayOfWeek)
    {
        // Base points per day
        $basePoints = 10;
        
        // Bonus for consecutive days
        $consecutiveBonus = ($dayNumber - 1) * 5;
        
        // Weekend bonus
        $weekendBonus = ($dayOfWeek == 6 || $dayOfWeek == 7) ? 10 : 0;
        
        return $basePoints + $consecutiveBonus + $weekendBonus;
    }

    /**
     * Add points to customer
     */
    private function addPointsToCustomer($customerId, $points)
    {
        try {
            return $this->customerModel->addPoints($customerId, $points);
        } catch (\Exception $e) {
            log_message('error', 'Add points error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent activities for customer (formatted for original dashboard)
     */
    private function getRecentActivities($customerId)
    {
        try {
            $db = \Config\Database::connect();
            $activities = [];
            
            // Get recent check-ins (last 5)
            $checkins = $db->table('customer_checkins')
                          ->where('customer_id', $customerId)
                          ->orderBy('created_at', 'DESC')
                          ->limit(5)
                          ->get()
                          ->getResultArray();
            
            foreach ($checkins as $checkin) {
                $activities[] = [
                    'type' => 'checkin',
                    'type_color' => 'success',
                    'icon' => 'calendar-check',
                    'title' => 'Daily Check-in Completed',
                    'time_ago' => $this->timeAgo($checkin['created_at']),
                    'reward' => '+' . $checkin['reward_points'] . ' pts'
                ];
            }
            
            // Get recent bonus claims (last 5)
            $claims = $db->table('bonus_claims')
                        ->where('customer_id', $customerId)
                        ->orderBy('created_at', 'DESC')
                        ->limit(5)
                        ->get()
                        ->getResultArray();
            
            foreach ($claims as $claim) {
                $activities[] = [
                    'type' => 'claim',
                    'type_color' => 'info',
                    'icon' => 'gift',
                    'title' => 'Reward Claimed: ' . $claim['bonus_type'],
                    'time_ago' => $this->timeAgo($claim['created_at']),
                    'reward' => ucfirst($claim['status'])
                ];
            }
            
            // Sort by date
            usort($activities, function($a, $b) {
                return strcmp($b['time_ago'], $a['time_ago']);
            });
            
            return array_slice($activities, 0, 10);
            
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
     * Get monthly checkins count
     */
    private function getMonthlyCheckinsCount($customerId)
    {
        try {
            $db = \Config\Database::connect();
            $currentMonth = date('Y-m');
            
            $count = $db->table('customer_checkins')
                       ->where('customer_id', $customerId)
                       ->where('checkin_date >=', $currentMonth . '-01')
                       ->where('checkin_date <=', $currentMonth . '-31')
                       ->countAllResults();
            
            return $count;
            
        } catch (\Exception $e) {
            log_message('error', 'Get monthly checkins error: ' . $e->getMessage());
            return 0;
        }
    }

    public function getWheelData()
    {
        $session = session();
        $customerId = $session->get('customer_id');

        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        try {
            $db = \Config\Database::connect();

            // Count today's spins
            $spinCount = $db->table('spin_history')
                ->where('customer_id', $customerId)
                ->where('DATE(spin_time)', date('Y-m-d'))
                ->countAllResults();

            $maxSpinsPerDay = 3;
            $remainingSpins = max(0, $maxSpinsPerDay - $spinCount);

            // Fetch wheel items from the database
            $wheelItemsRaw = $db->table('wheel_items')
                ->where('is_active', 1)
                ->orderBy('order', 'ASC')
                ->get()
                ->getResultArray();

            // Transform data to match JS expectations
            $wheelItems = array_map(function ($item) {
                return [
                    'item_id' => (int) $item['item_id'],
                    'item_name' => $item['item_name'],
                    'item_prize' => (float) $item['item_prize'],
                    'item_types' => $item['item_types'],
                    'winning_rate' => (float) $item['winning_rate'],
                    'order' => (int) $item['order']
                ];
            }, $wheelItemsRaw);

            return $this->response->setJSON([
                'success' => true,
                'spins_remaining' => $remainingSpins,
                'wheel_items' => $wheelItems,
                'spin_sound' => ['enabled' => true], // You can fetch these from DB if needed
                'win_sound' => ['enabled' => true]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'getWheelData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load wheel data'
            ]);
        }
    }
}